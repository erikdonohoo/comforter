<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCommitRequest;
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
        $coverage = 0;
        if ($request->hasFile('lcov')) {
            $coverage = $this->coverage->getCoverageFromLCOV($request->file('lcov')->getContent());
        }

        return $coverage;
    }
}
