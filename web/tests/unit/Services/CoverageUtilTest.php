<?php
namespace Services;

use App\Services\CoverageUtil;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use lcov\BranchCoverage;
use lcov\FunctionCoverage;
use lcov\LineCoverage;
use lcov\Record;
use lcov\Report;
use Mockery;
use Mockery\MockInterface;

/**
 * CoverageUtilTest class
 *
 * @property CoverageUtil $util
 * @property MockInterface $mockReport
 */
class CoverageUtilTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        $this->mockReport = Mockery::mock(Report::class);
        App::instance(Report::class, $this->mockReport);
        $this->util = App::make(CoverageUtil::class);
    }

    private function makeFakeReport (): Report
    {
        $report = new Report();
        $record = new Record('someFile.php');
        $branch = new BranchCoverage(5, 4);
        $fn = new FunctionCoverage(5, 4);
        $line = new LineCoverage(5, 5);
        $record->branches = $branch;
        $record->functions = $fn;
        $record->lines = $line;
        $report->records = new Collection([$record]);
        return $report;
    }

    public function testGetCoverageFromLcovResults()
    {
        $this->mockReport
            ->shouldReceive('fromCoverage')
            ->andReturn($this->makeFakeReport());

        $result = $this->util->getCoverageFromLCOV('lcov.info');
        $this->assertEquals($result['totalLines'], 15);
        $this->assertEquals($result['totalCovered'], 13);
    }
}
