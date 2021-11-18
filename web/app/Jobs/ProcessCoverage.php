<?php

namespace App\Jobs;

use App\Models\App;
use App\Models\Commit;
use App\Services\CoverageUtil;
use Gitlab\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * ProcessCoverage class
 *
 * @property Commit $commit
 * @property array $data
 */
class ProcessCoverage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $commit;
    public $data;

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
        $gitlabProject = $gitlabClient->projects()->show($this->data['project_id']);

        /** @var App $app */
        // Create new app if necessary
        $app = App::updateOrCreate([
            'gitlab_project_id' => $this->data['project_id'],
            'name' => $this->data['project_name'] ?? $gitlabProject['name'],
        ], [
            'primary_branch_name' => $gitlabProject['default_branch'],
            'repo_path' => $gitlabProject['path'],
            'namespace' => $gitlabProject['namespace']['path']
        ]);

        // Save commit
        $this->commit = Commit::updateOrCreate([
            'app_id' => $app->getKey(),
            'branch_name' => $this->commit->branch_name,
            'sha' => $this->commit->sha
        ], [
            'coverage' => $this->commit->coverage,
            'total_lines' => $this->commit->total_lines,
            'total_lines_covered' => $this->commit->total_lines_covered
        ]);

        // Get last known commit info
        /** @var Commit $lastCommit */
        $lastCommit = null;
        if (!empty($this->data['mergeBase'])) {
            /** @var Commit $lastCommit */
            $lastCommit = Commit::whereSha($this->data['mergeBase'])
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

        $this->commit->comparison_sha = $lastCommit->sha;
        $this->commit->save();

        // Compare coverage
        Log::info('Comparing Commits', [
            'currentCommit' => $this->commit,
            'compareCommit' => $lastCommit
        ]);

        $coverageChange = $util->roundCoverage(floatval($this->commit->coverage) - floatval($lastCommit->coverage));
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
            'ref' => isset($this->data['mergeRequestId']) ? "refs/merge-requests/{$this->data['mergeRequestId']}/head" : $this->commit->branch_name,
            'name' => "comforter/{$app->name}",
            'description' => $description,
            'target_url' => config('app.url')
        ]);
    }
}
