<?php

namespace Tests\Feature\Models;

use App\Models\App;
use App\Models\Commit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommitTest extends TestCase
{
    use RefreshDatabase;

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
