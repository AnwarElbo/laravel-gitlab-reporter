<?php

namespace Xolvio\GitlabReport;

/**
 * @package Xolvio\GitlabReport
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/gitlab-report.php' => config_path('gitlab-report.php'),
        ], 'gitlab-report');
    }

    public function register()
    {
        $this->mergeConfigFrom( __DIR__ . '/../config/gitlab-report.php', 'gitlab-report');

        $this->app->singleton(GitlabReportService::class, function() {
            $url            = env('GITLAB_REPORT_URL');
            $token          = env('GITLAB_REPORT_TOKEN');
            $project_id     = env('GITLAB_REPORT_PROJECT_ID');
            $labels         = env('GITLAB_REPORT_LABELS');

            return new GitlabReportService($url, $token, $project_id, $labels);
        });

        $this->app->alias(GitlabReportService::class, 'gitlab.report');
    }

    public function provides()
    {
        return ['gitlab.report', GitlabReportService::class];
    }
}