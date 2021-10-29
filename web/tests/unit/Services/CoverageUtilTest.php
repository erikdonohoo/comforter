<?php
namespace Services;

use App\Services\CoverageUtil;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Lcov\BranchCoverage;
use Lcov\FunctionCoverage;
use Lcov\LineCoverage;
use Lcov\Record;
use Lcov\Report;
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
        $branch = new BranchCoverage(5, 4);
        $fn = new FunctionCoverage(5, 4);
        $line = new LineCoverage(5, 5);
        $record = new Record('someFile.php', $fn, $branch, $line);
        $report = new Report('test', [$record]);
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
