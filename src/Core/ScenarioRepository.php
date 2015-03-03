<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Dispatchable;

/**
 * Class ScenarioRepository
 *
 * @package Keios\Apparatus
 */
class ScenarioRepository
{
    /**
     * @var \Keios\Apparatus\Core\ScenarioFactory
     */
    protected $scenarioFactory;

    /**
     * @var array
     */
    protected $scenarioCache = [];

    /**
     * @param \Keios\Apparatus\Core\ScenarioFactory $scenarioFactory
     */
    public function __construct(ScenarioFactory $scenarioFactory)
    {
        $this->scenarioFactory = $scenarioFactory;
    }

    /**
     * @param \Keios\Apparatus\Contracts\Dispatchable $event
     *
     * @return mixed
     */
    public function findHandlerScenarioFor(Dispatchable $event)
    {
        $eventName = $event->getEventName();

        $this->scenarioCache[$eventName] = $this->scenarioFactory->findHandlerScenarioFor($event);

        return $this->scenarioCache[$eventName];
    }

} 