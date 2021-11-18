<?php

use App\Jobs\ProcessCoverage;
use App\Models\App as ModelsApp;
use App\Models\Commit;
use App\Services\CoverageUtil;
use Codeception\Util\HttpCode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

/**
 * CoverageControllerCest class
 *
 * @property MockInterface $coverageMock
 */
class CoverageControllerCest
{
    public function _before ()
    {
        Bus::fake([ProcessCoverage::class]);
        Storage::fake();
        $this->coverageMock = Mockery::mock(CoverageUtil::class);
        App::instance(CoverageUtil::class, $this->coverageMock);
    }

    public function testAddNewCoverageHappyPath (ApiTester $I)
    {
        $this->coverageMock->shouldReceive('getCoverageFromLCOV')->andReturn([
            'totalLines' => 100,
            'totalCovered' => 99,
            'coverage' => '99.999'
        ]);
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $I->sendPOST('api/commits', [
            'branch' => 'master',
            'commit' => 'hash',
            'project' => 1,
            'name' => 'test',
            'merge-base' => 'base',
            'merge-request-iid' => 'iid',
        ], [
            'zip' => UploadedFile::fake()->create('zip.zip', 1024),
            'lcov' => UploadedFile::fake()->create('lcov.info', 1024)
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        Bus::assertDispatched(function (ProcessCoverage $job) {
            return $job->data['project_id'] === '1' &&
                $job->data['project_name'] === 'test' &&
                $job->data['mergeRequestId'] === 'iid' &&
                $job->data['mergeBase'] === 'base';
        });
    }

    public function testGetAppHappyPath (ApiTester $I)
    {
        $app = factory(ModelsApp::class)->create();
        $commits = factory(Commit::class, 2)->create(['app_id' => $app->getKey()]);
        $baseCommit = $commits[0];
        $otherCommit = $commits[1];
        $otherCommit->comparison_sha = $baseCommit->sha;
        $otherCommit->save();

        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $I->sendGET("api/apps/{$app->getKey()}");
        $I->seeResponseCodeIs(HttpCode::OK);

        $result = new ModelsApp(json_decode($I->grabResponse(), true));
        $commit = Collection::make($result->commits)->firstWhere('id', $otherCommit->getKey());
        $I->assertSame($commit['base_commit']['id'], $baseCommit->id);
    }
}
