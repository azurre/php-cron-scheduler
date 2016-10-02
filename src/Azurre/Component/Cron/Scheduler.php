<?php
/**
 * @date    23.07.2015
 * @version 0.1
 * @author  Aleksandr Milenin admin@azrr.info
 */

namespace Azurre\Component\Cron;

use Yalesov\CronExprParser\Parser;

class Scheduler {

    protected
        $time,
        $pathToJobs = '',
        $jobs = [];

    /**
     * @param mixed $time
     */
    public function __construct($time = null)
    {
        $this->time = $time ? $time : time();
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setJobPath($path)
    {
        $this->pathToJobs = $path;

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
            if (Parser::matchTime($this->time, $job['expr'])) {
                call_user_func_array($job['callback'], [$this->pathToJobs]);
            }
        }

        return $this;
    }
}