<?php

namespace Xolvio\GitlabReport;

/**
 * @package Xolvio\GitlabReport
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return GitlabReportService::class;
    }
}