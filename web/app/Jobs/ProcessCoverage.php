<?php

namespace App\Jobs;

use App\Models\App;
use App\Models\Commit;
use App\Services\CoverageUtil;
use Gitlab\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;

/**
 * ProcessCoverage class
 *
 * @property Commit $commit
 * @property array $data
 */
class ProcessCoverage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    private $commit;
    private $data;

    const GITLAB_PENDING = 'pending';
    const GITLAB_SUCCESS = 'success';
    const GITLAB_FAILED = 'failed';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct (Commit $commit, array $data)
    {
        $this->commit = $commit;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle (Client $gitlabClient, CoverageUtil $util)
    {
        // First, create new app if necessary
        /** @var App $app */
        $app = App::whereGitlabProjectId($this->data['project_id'])->first();
        $gitlabProject = $gitlabClient->projects()->show($this->data['project_id']);

        /** @var App $app */
        $app = App::updateOrCreate([
            'gitlab_project_id' => $this->data['project_id'],
        ], [
            'name' => $this->data['project_name'] ?? $gitlabProject['name'],
            'primary_branch_name' => $gitlabProject['default_branch'],
            'project_url' => $gitlabProject['web_url']
        ]);

        // Save commit
        $this->commit->app()->associate($app)->save();

        // Get last known commit info
        /** @var Commit $lastCommit */
        $lastCommit = null;
        if (!empty($this->data['merge_base'])) {
            /** @var Commit $lastCommit */
            $lastCommit = Commit::whereSha($this->data['merge_base'])
                ->whereAppId($app->getKey())
                ->where('id', '!=', $this->commit->getKey())
                ->first();
        }

        // Fall back to default branch last coverage
        if (!$lastCommit) {
            $lastCommit = $app->getLatestCommit();
        }

        // If we have never received an initial commit on master,
        // Just default to this commit's details
        if (!$lastCommit) {
            $lastCommit = $this->commit;
        }

        // Compare coverage
        $coverageChange = $util->roundCoverage($this->commit->coverage - $lastCommit->coverage);
        $allowDrop = $util->allowCoverageDrop($this->commit, $lastCommit);
        $description = "Coverage is increased by {$coverageChange}%";
        $state = self::GITLAB_SUCCESS;

        // Handle Coverage Drop
        if ($coverageChange < 0) {
            $description = "Coverage is decreased by {$coverageChange}%";
            $state = $allowDrop ? self::GITLAB_SUCCESS : self::GITLAB_FAILED;
        }

        // Update commit status
        $gitlabClient->repositories()->postCommitBuildStatus($this->data['project_id'], $this->commit->sha, $state, [
            'ref' => $this->commit->branch_name,
            'name' => "comforter/{$app->name}",
            'description' => $description,
            'coverage' => $this->commit->coverage,
            'target_url' => config('app.url')
        ]);
    }
}
