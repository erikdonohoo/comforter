<?php

namespace App\Services;

use Illuminate\Support\Collection;
use lcov\Record;
use lcov\Report;

class CoverageUtil
{
    public function getCoverageFromLCOV (string $lcovString)
    {
        $report = Report::fromCoverage($lcovString);
        $result = Collection::make($report->records)->reduce(function ($lastNumbers, Record $next) {
            return [
                'totalLines' => $lastNumbers['totalLines'] +
                    $next->branches->found +
                    $next->functions->found +
                    $next->lines->found,
                'totalCovered' => $lastNumbers['totalCovered'] +
                    $next->branches->hit +
                    $next->functions->hit +
                    $next->lines->hit
            ];
        }, [
            'totalLines' => 0,
            'totalCovered' => 0
        ]);

        return $this->roundCoverage(($result['totalCovered'] / $result['totalLines']) * 100);
    }

    public function roundCoverage (float $coverage)
    {
        return round($coverage, 4);
    }
}
