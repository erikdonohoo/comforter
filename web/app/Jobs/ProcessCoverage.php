<?php

namespace App\Jobs;

use App\Models\App;
use App\Models\Commit;
use Gitlab\Client;
use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct (UploadedFile $zip, Commit $commit, int $projectId, string $projectName = null)
    {
        $this->zip = $zip;
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
            // App::create([
            //     'name' => $this->projectName
            // ])
        }
    }
}
