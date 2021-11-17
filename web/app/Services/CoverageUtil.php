<?php

namespace App\Services;

use App\Models\Commit;
use Illuminate\Support\Collection;
use App\Services\LcovParsing\Record;
use App\Services\LcovParsing\Report;

/**
 * CoverageUtil class
 *
 * @property Report $lcovParser
 */
class CoverageUtil
{
    private $lcovParser;

    public function __construct (Report $lcovParser)
    {
        $this->lcovParser = $lcovParser;
    }

    public function getCoverageFromLCOV (string $lcovString): array
    {
        $report = $this->lcovParser->fromCoverage($lcovString);
        $result = Collection::make($report->getRecords())->reduce(function ($lastNumbers, Record $next) {
            return [
                'totalLines' => $lastNumbers['totalLines'] +
                    $next->getBranches()->getFound() +
                    $next->getFunctions()->getFound() +
                    $next->getLines()->getFound(),
                'totalCovered' => $lastNumbers['totalCovered'] +
                    $next->getBranches()->getHit() +
                    $next->getFunctions()->getHit() +
                    $next->getLines()->getHit()
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
