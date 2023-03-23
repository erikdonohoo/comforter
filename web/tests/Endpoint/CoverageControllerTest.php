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
        $response = $this->postJson('api/commits', [
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

    public function testAddNewCoverageFromLines ()
    {
        $this->coverageMock->shouldReceive('getCoverageFromLines')->andReturn([
            'totalLines' => 100,
            'totalCovered' => 90,
            'coverage' => '90'
        ]);
        $this->withHeader('Accept', 'application/json');
        $this->withHeader('Content-Type', 'multipart/form-data');
        $response = $this->postJson('api/commits', [
            'branch' => 'master',
            'commit' => 'hash',
            'project' => 1,
            'name' => 'test',
            'totalLines' => 100,
            'totalCovered' => 90,
            'merge-base' => 'base',
            'merge-request-iid' => 'iid',
            'zip' => UploadedFile::fake()->create('zip.zip', 1024)
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['coverage' => '90']);

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
        $baseCommit->branch_name = 'master';
        $baseCommit->save();
        $otherCommit = $commits[1];
        $otherCommit->comparison_sha = $baseCommit->sha;
        $otherCommit->save();

        $this->withHeader('Accept', 'application/json');
        $this->withHeader('Content-Type', 'multipart/form-data');
        $response = $this->getJson("api/apps/{$app->getKey()}");
        $response->assertOk();

        $result = new ModelsApp(json_decode($response->baseResponse->content(), true));
        $commit = Collection::make($result->commits)->firstWhere('id', $otherCommit->getKey());
        static::assertSame($commit['base_commit']['id'], $baseCommit->id);
    }

    public function testGetAppsHappyPath ()
    {
        $apps = ModelsApp::factory()->count(3)->create();
        $apps->each(fn ($app) =>
            Commit::factory()->create([
                'branch_name' => 'master',
                'app_id' => $app->getKey()
            ])
        );

        $response = $this->getJson('api/apps');
        $response->assertOk();
        $json = $response->json();
        $compareApp = $apps->first();
        $responseApp = new ModelsApp(collect($json)->firstWhere('id', $compareApp->getKey()));
        static::assertNotNull($responseApp);
        static::assertArrayHasKey('latest_commit', $responseApp);
    }
}
