<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCommitRequest;
use App\Jobs\ProcessCoverage;
use App\Models\App;
use App\Models\Commit;
use App\Services\CoverageUtil;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use ZipArchive;

/**
 * CoverageController class
 *
 * @property CoverageUtil $coverage
 */
class CoverageController extends Controller
{

    public function __construct(CoverageUtil $coverage)
    {
        $this->coverage = $coverage;
    }

    /**
     * Add coverage info to Comforter
     *
     * @param AddCommitRequest $request
     * @return \Illuminate\Http\Response
     */
    public function addCommit (AddCommitRequest $request)
    {
        Log::info('Coverage Request', $request->all());

        // Process LCOV if present
        if ($request->hasFile('lcov')) {
            $coverageInfo = $this->coverage->getCoverageFromLCOV($request->file('lcov')->getContent());
        } else {
            $coverageInfo = $this->coverage->getCoverageFromLines($request->totalLines, $request->totalCovered);
        }

        // Find or make new commit
        $commit = new Commit([
            'sha' => $request->commit,
            'branch_name' => $request->branch,
            'coverage' => $coverageInfo['coverage'],
            'total_lines' => $coverageInfo['totalLines'],
            'total_lines_covered' => $coverageInfo['totalCovered']
        ]);

        if ($request->zip) {
            $zip = new ZipArchive();
            $result = $zip->open($request->zip, ZipArchive::CREATE);

            if ($result !== true) {
                Log::critical('Failed to extract zip archive', [
                    'request' => $request->all(),
                    'error' => $result
                ]);
                throw new HttpException(Response::HTTP_FAILED_DEPENDENCY, 'Could not unzip coverage archive: ' . json_encode([
                    'request' => $request->all(),
                    'error' => $result
                ]));
            }

            // First item is always a folder we want to remove
            // $uuid = Str::uuid();
            // $tmpPath = "/tmp/{$uuid}";
            $newPath = "coverage/{$request->name}/{$request->branch}";
            Storage::disk('public')->makeDirectory($newPath);
            // $dirName = trim($zip->getNameIndex(0), '/');
            $zip->extractTo($newPath);
            $zip->close();
            // File::copyDirectory("{$tmpPath}/{$dirName}", public_path("{$newPath}"));
            // File::deleteDirectory("{$newPath}/{$dirName}");
        }

        // Dispatch job to handle GitLab communication and commit update
        ProcessCoverage::dispatch($commit, [
            'project_id' => $request->project,
            'project_name' => $request->name,
            'mergeRequestId' => $request->input('merge-request-iid'),
            'coverageInfo' => $coverageInfo,
            'mergeBase' => $request->input('merge-base')
        ]);

        // Send coverage back to requester
        return ['coverage' => $coverageInfo['coverage']];
    }

    /**
     * Get information for a specific app
     *
     * @param App $app
     * @return void
     */
    public function getApp (App $app)
    {
        $app = $app->load(['commits' => function ($query) {
            $query->limit(50);
            $query->orderByDesc('updated_at');
        }]);

        $app->commits->each(function (Commit $commit) {
            $commit->setRelation('baseCommit', $commit->getBaseCommit());
        });

        $app->setRelation('latestCommit', $app->getLatestCommit());

        return $app;
    }

    public function getApps ()
    {
        $apps = App::all();
        return $apps->each(function (App $app) {
            $app->setRelation('latestCommit', $app->getLatestCommit());
        });
    }
}
