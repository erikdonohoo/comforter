<?php
namespace Models;

use App\Models\App;
use App\Models\Commit;

class CommitTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testCommitRelation()
    {
        $app = factory(App::class)->create();
        /** @var Commit $commit */
        $commit = factory(Commit::class)->make();
        $commit->app()->associate($app)->save();
        $commit->refresh();
        $this->assertTrue($app->is($commit->app));
    }

    public function testBaseCommitRelation()
    {
        $app = factory(App::class)->create();
        /** @var Commit $commit */
        $commit = factory(Commit::class)->create(['app_id' => $app->getKey()]);
        $otherCommit = factory(Commit::class)->create(['app_id' => $app->getKey()]);
        $commit->comparison_sha = $otherCommit->sha;
        $commit->save();
        static::assertSame($otherCommit->getKey(), $commit->getBaseCommit()->getKey());
    }

    public function testBaseCommitMissingComparisonSha()
    {
        $app = factory(App::class)->create();
        /** @var Commit $commit */
        $commit = factory(Commit::class)->create(['app_id' => $app->getKey()]);
        static::assertNotNull($commit->getBaseCommit());
    }
}
