<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCommitRequest;
use App\Jobs\ProcessCoverage;
use App\Models\App;
use App\Models\Commit;
use App\Services\CoverageUtil;

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
            $coverage = $this->coverage->getCoverageFromLCOV($request->file('lcov')->getContent());
        } else {
            $coverage = $this->coverage->roundCoverage($request->coverage);
        }

        $commit = Commit::make([
            'sha' => $request->sha,
            'coverage' => $coverage,
            'branch_name' => $request->branch
        ]);

        // TODO: Unzip and put code in correct directory (or S3)

        // Dispatch job to handle GitLab communication and commit update
        ProcessCoverage::dispatch($commit, $request->project_id, $request->project_name);

        // Send coverage back to requester
        return ['coverage' => $coverage];
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
            $query->limit(100);
            $query->orderByDesc('created_at');
        }]);

        $app->setRelation('latestCommit', $app->getLatestCommit());

        return $app;
    }
}
