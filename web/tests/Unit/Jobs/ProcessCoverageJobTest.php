<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessCoverage;
use App\Models\App;
use App\Models\Commit;
use Gitlab\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\App as FacadesApp;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * ProcessCoverageJobTest class
 */
class ProcessCoverageJobTest extends TestCase
{
    use DatabaseTransactions;

    private Commit $commit;
    private Commit $originalCommit;
    private array $data;
    private MockInterface $gitlabMock;
    private App $coverageApp;

    protected function setUp (): void
    {
        parent::setUp();
        $this->gitlabMock = Mockery::mock(Client::class);
        $this->coverageApp = App::factory()->create();
        // Initial commit
        $this->originalCommit = Commit::factory()->create([
            'branch_name' => 'master',
            'total_lines' => 10,
            'total_lines_covered' => 5,
            'coverage' => '50.000',
            'app_id' => $this->coverageApp->getKey()
        ]);
        $this->commit = Commit::factory()->make([
            'total_lines' => 10,
            'total_lines_covered' => 6,
            'coverage' => '60.000',
            'app_id' => $this->coverageApp->getKey()
        ]);
        $this->data = [
            'project_id' => $this->coverageApp->gitlab_project_id,
            'project_name' => $this->coverageApp->name
        ];
        FacadesApp::instance(Client::class, $this->gitlabMock);
    }

    public function testSimplePath ()
    {
        $this->gitlabMock->shouldReceive('projects->show')->once()->andReturn([
            'default_branch' => 'master',
            'path' => 'project/prjoect',
            'namespace' => [
                'path' => 'path'
            ]
        ]);

        $this->gitlabMock->shouldReceive('repositories->postCommitBuildStatus')->withArgs([
            $this->data['project_id'],
            $this->commit->sha,
            ProcessCoverage::GITLAB_SUCCESS,
            [
                'ref' => $this->commit->branch_name,
                'name' => "comforter/{$this->coverageApp->name}",
                'description' => 'Coverage is increased by 10%',
                'target_url' => config('app.url') . "/projects/{$this->commit->app_id}"
            ]
        ])->once();


        ProcessCoverage::dispatchSync($this->commit, $this->data);
        $this->commit = Commit::whereSha($this->commit->sha)->first();
        static::assertSame($this->commit->comparison_sha, $this->originalCommit->sha);
    }

    public function testCoverageDrop ()
    {
        $this->gitlabMock->shouldReceive('projects->show')->once()->andReturn([
            'default_branch' => 'master',
            'path' => 'project/prjoect',
            'namespace' => [
                'path' => 'path'
            ]
        ]);

        $this->gitlabMock->shouldReceive('repositories->postCommitBuildStatus')->withArgs([
            $this->data['project_id'],
            $this->commit->sha,
            ProcessCoverage::GITLAB_FAILED,
            [
                'ref' => $this->commit->branch_name,
                'name' => "comforter/{$this->coverageApp->name}",
                'description' => 'Coverage is decreased by -10%',
                'target_url' => config('app.url') . "/projects/{$this->commit->app_id}"
            ]
        ])->once();

        // Make it a drop
        $this->commit->total_lines = 10;
        $this->commit->total_lines_covered = 4;
        $this->commit->coverage = '40.0000';

        ProcessCoverage::dispatchSync($this->commit, $this->data);
        $this->commit = Commit::whereSha($this->commit->sha)->first();
        static::assertSame($this->commit->comparison_sha, $this->originalCommit->sha);
    }

    public function testMergeRequestRef ()
    {
        $this->data['mergeRequestId'] = '1';
        $this->gitlabMock->shouldReceive('projects->show')->once()->andReturn([
            'default_branch' => 'master',
            'path' => 'project/prjoect',
            'namespace' => [
                'path' => 'path'
            ]
        ]);

        $this->gitlabMock->shouldReceive('repositories->postCommitBuildStatus')->withArgs([
            $this->data['project_id'],
            $this->commit->sha,
            ProcessCoverage::GITLAB_SUCCESS,
            [
                'ref' => 'refs/merge-requests/1/head',
                'name' => "comforter/{$this->coverageApp->name}",
                'description' => 'Coverage is increased by 10%',
                'target_url' => config('app.url') . "/projects/{$this->commit->app_id}"
            ]
        ])->once();

        ProcessCoverage::dispatchSync($this->commit, $this->data);
    }
}
