<?php

use App\Jobs\ProcessCoverage;
use App\Models\Commit;
use App\Services\CoverageUtil;
use Codeception\Util\HttpCode;
use Illuminate\Http\UploadedFile;
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
        Storage::fake();
        Bus::fake([ProcessCoverage::class]);
        $this->coverageMock = Mockery::mock(CoverageUtil::class);
        App::instance(CoverageUtil::class, $this->coverageMock);
    }

    public function testAddNewCoverageHappyPath (ApiTester $I)
    {
        $this->coverageMock->shouldReceive('getCoverageFromLCOV')->andReturn([
            'totalLines' => 100,
            'totalCovered' => 99
        ]);
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $I->sendPOST('api/commits', [
            'branch' => 'master',
            'commit' => 'hash',
            'project' => 1,
            'name' => 'test',
            'mergeBase' => 'base',
            'mergeRequestIID' => 'iid',
        ], [
            'zip' => UploadedFile::fake()->create('zip.zip', 1024),
            'lcov' => UploadedFile::fake()->create('lcov.info', 1024)
        ]);
        $I->seeResponseCodeIs(HttpCode::CREATED);

        Bus::assertDispatched(ProcessCoverage::class, function (Commit $commit, array $data) {

        });
    }
}
