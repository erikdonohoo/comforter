<?php

namespace App\Jobs;

use App\Models\App;
use App\Models\Commit;
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
    public function handle (Client $gitlabClient)
    {
        // First, create new app if necessary
        /** @var App $app */
        $app = App::whereGitlabProjectId($this->data['project_id'])->first();

        if (!$app) {

            $gitlabProject = $gitlabClient->projects()->show($this->data['project_id']);
            /** @var App $app */
            $app = App::create([
                'name' => $this->data['project_name'] ?? $gitlabProject['name'],
                'gitlab_project_id' => $this->projectId,
                'primary_branch_name' => $gitlabProject['default_branch']
            ]);
        }

        // Tests.
        // 1. Creates app first time
        // 2. Adds a commit first time
        // 3. Updates a commit second time

        $this->commit->app()->associate($app);
        /** @var Commit $commit */
        $this->commit = Commit::updateOrCreate([
            'sha' => $this->commit->sha,
            'app_id' => $this->commit->app_id
        ], [
            'coverage' => $this->commit->coverage,
            'branch_name' => $this->commit->branch_name
        ]);

        // Update commit status
        $gitlabClient->repositories()->postCommitBuildStatus($this->data['project_id'], $this->commit->sha, 'pending', [
            'ref' => $this->data['mergeRequestId'] ? "refs/merge-requests/{$this->data['mergeRequestId']}/head" : $this->commit->branch_name,
            'name' => "comforter/{$app->name}",
            'description' => 'Comforter is calculating...',
            'coverage' => $this->commit->coverage
        ]);

        // Get last known commit info
        /** @var Commit $lastCommit */
        $lastCommit = null;
        if (isset($this->data['merge_base'])) {
            /** @var Commit $lastCommit */
            $lastCommit = Commit::whereSha($this->data['merge_base'])->where->first();
        }

        if (!$lastCommit) {}
    }
}
