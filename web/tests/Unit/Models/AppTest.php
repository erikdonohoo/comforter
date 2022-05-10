<?php

namespace Tests\Unit\Models;

use App\Models\App;
use App\Models\Commit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AppTest extends TestCase
{
    use DatabaseTransactions;

    private App $appModel;
    private Carbon $now;

    protected function setUp (): void
    {
        parent::setUp();
        $this->now = Carbon::now();
        $this->appModel = App::factory()->create([
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
        $commit = Commit::factory()->make();
        $commit->app()->associate($this->appModel)->save();
        $commitList = $this->appModel->commits;
        $this->assertCount(1, $commitList);
        $listCommit = $commitList->first();
        $this->assertTrue($commit->is($listCommit));
    }

    public function testGetLatestCommit ()
    {
        /** @var Commit $commit */
        $commit = Commit::factory()->make(['branch_name' => 'master']);
        $commit->app()->associate($this->appModel)->save();
        $latestCommit = $this->appModel->getLatestCommit();
        $this->assertTrue($commit->is($latestCommit));

        $newCommit = Commit::factory()->make([
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
        $commit = Commit::factory()->make(['branch_name' => 'master']);
        $commit->app()->associate($this->appModel)->save();
        $this->assertEquals($commit->coverage, $this->appModel->coverage);
    }
}
