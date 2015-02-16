<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Dispatchable;
use Keios\Apparatus\Contracts\Runnable;

class ScenarioRunner
{
    protected $dispatcher;

    public function run(Runnable $scenario, Dispatchable $event)
    {
        $scenario->setDispatcher($this->dispatcher);
        $scenario->inject($event);

        return $scenario->run();
    }

    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
} 