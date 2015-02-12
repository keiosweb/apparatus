<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Dispatchable;
use Keios\Apparatus\Contracts\Playable;

class ScenarioRunner
{
    protected $dispatcher;

    public function run(Playable $scenario, Dispatchable $dispatch)
    {
        $scenario->setDispatcher($this->dispatcher);

        return $scenario->play($dispatch->getEventData(), $dispatch->getExpectedReactions());
    }

    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
} 