<?php

namespace App\Services;

use App\Models\Commit;
use Illuminate\Support\Collection;
use lcov\Record;
use lcov\Report;

class CoverageUtil
{
    public function getCoverageFromLCOV (string $lcovString): array
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

        $result['coverage'] = $this->roundCoverage(($result['totalCovered'] / $result['totalLines']) * 100);

        return $result;
    }

    public function getCoverageFromLines (string $coverage, string $totalLines, string $totalCovered): array
    {
        return [
            'coverage' => $this->roundCoverage(floatval($coverage)),
            'totalLines' => intval($totalLines),
            'totalCovered' => intval($totalCovered)
        ];
    }

    public function allowCoverageDrop (Commit $newCommit, Commit $oldCommit): bool
    {
        return $newCommit->total_lines < $oldCommit->total_lines && (
            $oldCommit->total_lines - $oldCommit->total_lines_covered >=
            $newCommit->total_lines - $newCommit->total_lines_covered
        );
    }

    public function roundCoverage (float $coverage)
    {
        return round($coverage, config('app.coverageToDecimalPoint'));
    }
}
