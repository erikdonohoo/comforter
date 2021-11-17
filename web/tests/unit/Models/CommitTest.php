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
}
