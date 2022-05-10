<?php

namespace Tests\Endpoint;

use App\Jobs\ProcessCoverage;
use App\Models\App as ModelsApp;
use App\Models\Commit;
use App\Services\CoverageUtil;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * CoverageControllerCest class
 */
class CoverageControllerTest extends TestCase
{
    use DatabaseTransactions;

    private MockInterface $coverageMock;

    public function setUp (): void
    {
        parent::setUp();
        Bus::fake([ProcessCoverage::class]);
        Storage::fake();
        $this->coverageMock = Mockery::mock(CoverageUtil::class);
        App::instance(CoverageUtil::class, $this->coverageMock);
    }

    public function testAddNewCoverageHappyPath ()
    {
        $this->coverageMock->shouldReceive('getCoverageFromLCOV')->andReturn([
            'totalLines' => 100,
            'totalCovered' => 99,
            'coverage' => '99.999'
        ]);
        $this->withHeader('Accept', 'application/json');
        $this->withHeader('Content-Type', 'multipart/form-data');
        $response = $this->post('api/commits', [
            'branch' => 'master',
            'commit' => 'hash',
            'project' => 1,
            'name' => 'test',
            'merge-base' => 'base',
            'merge-request-iid' => 'iid',
            'zip' => UploadedFile::fake()->create('zip.zip', 1024),
            'lcov' => UploadedFile::fake()->create('lcov.info', 1024)
        ]);

        $response->assertOk();

        Bus::assertDispatched(function (ProcessCoverage $job) {
            return $job->data['project_id'] === 1 &&
                $job->data['project_name'] === 'test' &&
                $job->data['mergeRequestId'] === 'iid' &&
                $job->data['mergeBase'] === 'base';
        });
    }

    public function testGetAppHappyPath ()
    {
        $app = ModelsApp::factory()->create();
        $commits = Commit::factory()->count(2)->create(['app_id' => $app->getKey()]);
        $baseCommit = $commits[0];
        $otherCommit = $commits[1];
        $otherCommit->comparison_sha = $baseCommit->sha;
        $otherCommit->save();

        $this->withHeader('Accept', 'application/json');
        $this->withHeader('Content-Type', 'multipart/form-data');
        $response = $this->get("api/apps/{$app->getKey()}");
        $response->assertOk();

        $result = new ModelsApp(json_decode($response->baseResponse->content(), true));
        $commit = Collection::make($result->commits)->firstWhere('id', $otherCommit->getKey());
        static::assertSame($commit['base_commit']['id'], $baseCommit->id);
    }
}
