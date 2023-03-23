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
    private array $data;
    private MockInterface $gitlabMock;

    protected function setUp (): void
    {
        parent::setUp();
        $this->gitlabMock = Mockery::mock(Client::class);
        $app = App::factory()->create([
            'gitlab_project_id' => 1,
            'name' => 'Test'
        ]);
        $this->commit = Commit::factory()->make([
            'total_lines' => 10,
            'total_lines_covered' => 5,
            'coverage' => '50.000',
            'app_id' => $app->getKey()
        ]);
        $this->data = [
            'project_id' => 1,
            'project_name' => 'Test'
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
                'name' => "comforter/Test",
                'description' => 'Coverage is increased by 0%',
                'target_url' => config('app.url') . "/projects/{$this->commit->app_id}"
            ]
        ])->once();


        ProcessCoverage::dispatchSync($this->commit, $this->data);
        $this->commit = Commit::whereSha($this->commit->sha)->first();
        static::assertSame($this->commit->comparison_sha, $this->commit->sha);
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
                'name' => "comforter/Test",
                'description' => 'Coverage is increased by 0%',
                'target_url' => config('app.url') . "/projects/{$this->commit->app_id}"
            ]
        ])->once();


        ProcessCoverage::dispatchSync($this->commit, $this->data);
    }
}
