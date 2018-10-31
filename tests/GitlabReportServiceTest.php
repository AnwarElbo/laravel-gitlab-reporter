<?php
namespace tests;

use Gitlab\Model\Issue;
use PHPUnit\Framework\TestCase;
use Xolvio\GitlabReport\GitlabReportService;

class GitlabReportServiceTest extends TestCase
{
    /**
     * @var GitlabReportService
     */
    private $gitlab_report_service;

    public function setUp()
    {
        $url         = '';
        $token       = '';
        $project_id  = '';
        $labels      = '';

        $this->gitlab_report_service = new GitlabReportService(
            $url,
            $token,
            $project_id,
            $labels
        );
    }

    public function testReport()
    {
        $exception = new \RuntimeException();
        $result = $this->gitlab_report_service->report($exception);

        self::assertInstanceOf(Issue::class, $result);
    }
}