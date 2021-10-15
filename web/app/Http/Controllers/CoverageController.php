<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCommitRequest;
use App\Jobs\ProcessCoverage;
use App\Models\App;
use App\Models\Commit;
use App\Services\CoverageUtil;
use Illuminate\Http\Response;
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
        // Process LCOV if present
        if ($request->hasFile('lcov')) {
            $coverageInfo = $this->coverage->getCoverageFromLCOV($request->file('lcov')->getContent());
        } else {
            $coverageInfo = $this->coverage->getCoverageFromLines($request->coverage, $request->totalLines, $request->totalCovered);
        }

        // Find or make new commit
        $commit = Commit::firstOrNew([
            'sha' => $request->commit,
            'branch_name' => $request->branch,
        ]);

        $commit->fill([
            'coverage' => $coverageInfo['coverage'],
            'total_lines' => $coverageInfo['totalLines'],
            'total_lines_covered' => $coverageInfo['totalCovered']
        ]);

        if ($request->zip) {
            $zip = new ZipArchive();
            if ($zip->open($request->zip)) {
                // First item is always a folder we want to remove
                $initialFolderName = $zip->getNameIndex(0);
                $uuid = Str::uuid();
                $root = config('filesystems.disks.public.root');
                $tmpPath = "{$root}/coverage/{$uuid}";
                $newPath = "coverage/{$request->name}/{$request->branch}";
                Storage::disk('public')->makeDirectory($newPath);
                $zip->extractTo($tmpPath);
                Storage::disk('public')->move("{$tmpPath}/{$initialFolderName}/*", "{$root}/{$newPath}");
                Storage::disk('public')->deleteDirectory($tmpPath);
                $zip->close();
            } else {
                Log::critical('Failed to extract zip archive', [
                    'request' => $request->all()
                ]);
                throw new HttpException(Response::HTTP_FAILED_DEPENDENCY, 'Could not unzip coverage archive');
            }
        }

        // Dispatch job to handle GitLab communication and commit update
        ProcessCoverage::dispatch($commit, [
            'project_id' => $request->project,
            'project_name' => $request->name,
            'merge_request_id' => $request->mergeRequestIID,
            'coverageInfo' => $coverageInfo
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

        $latestCommit = $app->getLatestCommit();

        // TODO: In the future, have each base commit be the REAL base
        $app->commits->each(function (Commit $commit) use ($latestCommit) {
            $commit->setRelation('baseCommit', $latestCommit);
        });

        $app->setRelation('latestCommit', $latestCommit);

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
