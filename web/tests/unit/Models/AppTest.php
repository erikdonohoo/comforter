<?php

namespace Models;

use App\Models\App;
use App\Models\Commit;
use Illuminate\Support\Carbon;

/**
 * AppTest class
 *
 * @property App $app
 * @property Carbon $now
 */
class AppTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        $this->now = Carbon::now();
        $this->app = factory(App::class)->create([
            'primary_branch_name' => 'master'
        ]);
        Carbon::setTestNow($this->now);
    }

    protected function _after()
    {
        Carbon::setTestNow();
    }

    public function testCommitsRelation ()
    {
        /** @var Commit $commit */
        $commit = factory(Commit::class)->make();
        $commit->app()->associate($this->app)->save();
        $commitList = $this->app->commits;
        $this->assertCount(1, $commitList);
        $listCommit = $commitList->first();
        $this->assertTrue($commit->is($listCommit));
    }

    public function testGetLatestCommit ()
    {
        /** @var Commit $commit */
        $commit = factory(Commit::class)->make(['branch_name' => 'master']);
        $commit->app()->associate($this->app)->save();
        $latestCommit = $this->app->getLatestCommit();
        $this->assertTrue($commit->is($latestCommit));

        $newCommit = factory(Commit::class)->make([
            'branch_name' => 'master',
            'created_at' => Carbon::now()->addHour()
        ]);
        $newCommit->app()->associate($this->app)->save();
        $latestCommit = $this->app->getLatestCommit();
        $this->assertFalse($commit->is($latestCommit));
        $this->assertTrue($newCommit->is($latestCommit));
    }

    public function testGetCoverageAttribute ()
    {
        /** @var Commit $commit */
        $commit = factory(Commit::class)->make(['branch_name' => 'master']);
        $commit->app()->associate($this->app)->save();
        $this->assertEquals($commit->coverage, $this->app->coverage);
    }
}
