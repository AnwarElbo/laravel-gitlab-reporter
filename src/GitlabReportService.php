<?php

namespace Xolvio\GitlabReport;

use Illuminate\Container\Container;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Gitlab\Client;
use Gitlab\Model\Project;
use Exception;
use Xolvio\GitlabReport\Reports\DatabaseReport;
use Xolvio\GitlabReport\Reports\DefaultReport;

class GitlabReportService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Project ID given in GitLab
     *
     * @var string
     */
    private $project_id;

    /**
     * Contains all the labels applied to an issue
     *
     * @var string
     */
    private $labels;

    /**
     * Current request
     *
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $reporters = [
        QueryException::class => DatabaseReport::class
    ];

    /**
     * @param string $url
     * @param string $token
     * @param string $project_id
     * @param string $labels
     */
    public function __construct(
        string $url,
        string $token,
        string $project_id,
        string $labels
    ) {
        $container = Container::getInstance();

        $this->client     = Client::create($url)->authenticate($token, Client::AUTH_URL_TOKEN);
        $this->request    = $container->make(Request::class);
        $this->project_id = $project_id;
        $this->labels     = $labels;
    }


    /**
     * GitlabReport function to report exceptions. This will generate a GitlabReport and send it to GitLab as issue under the project
     * @param Exception $exception
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function report(Exception $exception)
    {

        try {
            // Get current request
            $reporter = $this->reporter($exception);

            /** @var DefaultReport $report */
            $report   = new $reporter($exception, $this->request);
            $project  = new Project($this->project_id, $this->client);

            // Check if an issue exists with the same title and is currently open.
            $issues = $project->issues(['search' => "Identifier: `{$report->signature()}`", 'state' => 'opened']);

            if (! empty($issues)) {
                $issue = reset($issues);
                $issue->addComment('Occurred again');

                return $issue;
            }

            return $project->createIssue(
                $report->title(), [
                'description' => $report->description(),
                'labels'      => $this->labels
            ]);
        } catch (Exception $exp){
            throw $exp;
        }
    }

    /**
     * Returns the right reporter class based on the exception given
     *
     * @param Exception $exception
     *
     * @return mixed|string
     */
    private function reporter(Exception $exception){
        // Get right reporter
        $rc = DefaultReport::class;

        foreach($this->reporters as $key => $reporter){
            if(is_a($exception, $key)){
                $rc = $reporter;
            }
        }

        return $rc;
    }
}