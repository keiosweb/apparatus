<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Dispatchable;

class ScenarioRepository
{
    protected $scenarioFactory;

    protected $scenarioCache = [];

    public function __construct(ScenarioFactory $scenarioFactory)
    {
        $this->scenarioFactory = $scenarioFactory;
    }

    public function findHandlerScenarioFor(Dispatchable $event)
    {
        $eventName = $event->getEventName();

        $this->scenarioCache[$eventName] = $this->scenarioFactory->findHandlerScenarioFor($event);

        return $this->scenarioCache[$eventName];
    }

} 