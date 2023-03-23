<?php

namespace Tests\Unit\Models;

use App\Models\App;
use App\Models\Commit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CommitTest extends TestCase
{
    use DatabaseTransactions;

    public function testCommitRelation()
    {
        $app = App::factory()->create();
        /** @var Commit $commit */
        $commit = Commit::factory()->make();
        $commit->app()->associate($app)->save();
        $commit->refresh();
        $this->assertTrue($app->is($commit->app));
    }

    public function testBaseCommitRelation()
    {
        $app = App::factory()->create();
        /** @var Commit $commit */
        $commit = Commit::factory()->create(['app_id' => $app->getKey()]);
        $otherCommit = Commit::factory()->create(['app_id' => $app->getKey()]);
        $commit->comparison_sha = $otherCommit->sha;
        $commit->save();
        static::assertSame($otherCommit->getKey(), $commit->getBaseCommit()->getKey());
    }

    public function testBaseCommitMissingComparisonSha()
    {
        $app = App::factory()->create();
        /** @var Commit $commit */
        $commit = Commit::factory()->create(['app_id' => $app->getKey(), 'branch_name' => 'master']);
        static::assertNotNull($commit->getBaseCommit());
    }
}
