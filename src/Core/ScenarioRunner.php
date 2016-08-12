<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Dispatchable;
use Keios\Apparatus\Contracts\Runnable;

/**
 * Class ScenarioRunner
 *
 * @package Keios\Apparatus
 */
class ScenarioRunner
{
    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @param \Keios\Apparatus\Contracts\Runnable     $scenario
     * @param \Keios\Apparatus\Contracts\Dispatchable $event
     *
     * @return mixed
     */
    public function run(Runnable $scenario, Dispatchable $event)
    {
        $scenario->setDispatcher($this->dispatcher);
        $scenario->inject($event);

        return $scenario->run();
    }

    /**
     * @param \Keios\Apparatus\Core\Dispatcher $dispatcher
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
} 