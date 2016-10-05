<?php
/**
 * @date    23.07.2015
 * @version 1.1
 * @author  Aleksandr Milenin admin@azrr.info
 */

namespace Azurre\Component\Cron;

use Yalesov\CronExprParser\Parser;

class Scheduler {

    protected
        $startTime,
        $logsPath = './',
        $jobsPath = './jobs/',
        $jobs = [];

    /**
     * @param int $startTime
     */
    public function __construct($startTime = null)
    {
        $this->startTime = $startTime ? $startTime : time();
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setJobPath($path)
    {
        $this->jobsPath = $path;

        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setLogsPath($path)
    {
        $this->logsPath = $path;

        return $this;
    }

    /**
     * @param string   $expr
     * @param callable $callback
     *
     * @return $this
     */
    public function addJob($expr, $callback)
    {
        $this->jobs[] = ['expr' => $expr, 'callback' => $callback];

        return $this;
    }

    /**
     * @return $this
     */
    public function clearJobs()
    {
        $this->jobs[] = [];

        return $this;
    }


    /**
     * Run matched jobs
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function run()
    {
        foreach ($this->jobs as $job) {
            if (Parser::matchTime($this->startTime, $job['expr'])) {
                call_user_func_array($job['callback'], [$this->logsPath, $this->jobsPath]);
            }
        }

        return $this;
    }
}