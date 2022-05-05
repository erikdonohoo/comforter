<?php

namespace Tests\Feature\Models;

use App\Models\App;
use App\Models\Commit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AppTest extends TestCase
{
    use RefreshDatabase;

    private App $appModel;
    private Carbon $now;

    protected function setUp (): void
    {
        parent::setUp();
        $this->now = Carbon::now();
        $this->appModel = factory(App::class)->create([
            'primary_branch_name' => 'master'
        ]);
        Carbon::setTestNow($this->now);
    }

    protected function tearDown (): void
    {
        parent::tearDown();
        Carbon::setTestNow();
    }

    public function testCommitsRelation ()
    {
        /** @var Commit $commit */
        $commit = factory(Commit::class)->make();
        $commit->app()->associate($this->appModel)->save();
        $commitList = $this->appModel->commits;
        $this->assertCount(1, $commitList);
        $listCommit = $commitList->first();
        $this->assertTrue($commit->is($listCommit));
    }

    public function testGetLatestCommit ()
    {
        /** @var Commit $commit */
        $commit = factory(Commit::class)->make(['branch_name' => 'master']);
        $commit->app()->associate($this->appModel)->save();
        $latestCommit = $this->appModel->getLatestCommit();
        $this->assertTrue($commit->is($latestCommit));

        $newCommit = factory(Commit::class)->make([
            'branch_name' => 'master',
            'updated_at' => Carbon::now()->addHour()
        ]);
        $newCommit->app()->associate($this->appModel)->save();
        $latestCommit = $this->appModel->getLatestCommit();
        $this->assertFalse($commit->is($latestCommit));
        $this->assertTrue($newCommit->is($latestCommit));
    }

    public function testGetCoverageAttribute ()
    {
        /** @var Commit $commit */
        $commit = factory(Commit::class)->make(['branch_name' => 'master']);
        $commit->app()->associate($this->appModel)->save();
        $this->assertEquals($commit->coverage, $this->appModel->coverage);
    }
}
