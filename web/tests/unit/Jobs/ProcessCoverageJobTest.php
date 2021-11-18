<?php

namespace Jobs;

use App\Jobs\ProcessCoverage;
use App\Models\Commit;
use Gitlab\Client;
use Illuminate\Support\Facades\App;
use Mockery;
use Mockery\MockInterface;

/**
 * ProcessCoverageJobTest class
 *
 * @property Commit $commit
 * @property array $data
 * @property MockInterface $gitlabMock
 */
class ProcessCoverageJobTest extends \Codeception\Test\Unit
{
    private $commit;
    private $data;
    private $gitlabMock;

    protected function _before ()
    {
        $this->gitlabMock = Mockery::mock(Client::class);
        $this->commit = factory(Commit::class)->make([
            'total_lines' => 10,
            'total_lines_covered' => 5,
            'coverage' => '50.000'
        ]);
        $this->data = [
            'project_id' => 1,
            'project_name' => 'Test'
        ];
        App::instance(Client::class, $this->gitlabMock);
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
                'target_url' => config('app.url')
            ]
        ])->once();


        ProcessCoverage::dispatchNow($this->commit, $this->data);
        $this->commit = Commit::whereSha($this->commit->sha)->first();
        static::assertSame($this->commit->comparison_sha, $this->commit->sha);
    }
}
