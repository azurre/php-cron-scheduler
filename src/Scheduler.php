<?php
/**
 * @author Alex Milenin
 * @email  admin@azrr.info
 * @copyright Copyright (c)Alex Milenin (https://azrr.info/)
 */

namespace Azurre\Component\Cron;

class Scheduler {
    /** @var int|string */
    protected $startTime;
    protected $jobs = [];

    /**
     * @param string|numeric|null $startTime
     */
    public function __construct($startTime = null)
    {
        $this->startTime = $startTime ?: $_SERVER['REQUEST_TIME'];
    }

    /**
     * @param string|Expression $expr
     * @param callable $callback
     *
     * @return $this
     */
    public function addJob($expr, callable $callback)
    {
        $this->jobs[] = [$expr, $callback];
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
     * @return void
     */
    public function run()
    {
        foreach ($this->jobs as $job) {
            list($expr, $callback) = $job;
            if (Expression::matchTime($this->startTime, (string)$expr)) {
                $callback($expr);
            }
        }
    }
}