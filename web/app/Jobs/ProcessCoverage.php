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
 * @property UploadedFile $zip
 * @property Commit $commit
 * @property int $projectId
 * @property string $projectName
 */
class ProcessCoverage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    private $commit;
    private $projectId;
    private $projectName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct (Commit $commit, int $projectId, string $projectName = null)
    {
        $this->commit = $commit;
        $this->projectId = $projectId;
        $this->projectName = $projectName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle (Client $gitlabClient)
    {
        // First, create new app if necessary
        $app = App::whereGitlabProjectId($this->projectId)->first();

        if (!$app) {

            $gitlabProject = $gitlabClient->projects()->show($this->projectId);
            $app = App::create([
                'name' => $this->projectName ?? $gitlabProject['name'],
                'gitlab_project_id' => $this->projectId,
                'primary_branch_name' => $gitlabProject['default_branch']
            ]);
        }

        // Tests.
        // 1. Creates app first time
        // 2. Adds a commit first time
        // 3. Updates a commit second time

        $this->commit->app()->associate($app);
        $this->commit = Commit::updateOrCreate([
            'sha' => $this->commit->sha,
            'app_id' => $this->commit->app_id
        ], [
            'coverage' => $this->commit->coverage,
            'branch_name' => $this->commit->branch_name
        ]);
    }
}
