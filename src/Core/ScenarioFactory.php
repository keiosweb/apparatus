<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Exceptions\InvalidScenarioConfigurationException;
use Keios\Apparatus\Exceptions\NoHandlerScenarioFoundException;
use Keios\Apparatus\Contracts\LoaderInterface;
use Keios\Apparatus\Contracts\Dispatchable;

class ScenarioFactory
{
    protected $registeredScenarios;

    public function __construct(LoaderInterface $scenarioLoader)
    {
        $this->getScenarios($scenarioLoader);
    }

    public function findHandlerScenarioFor(Dispatchable $dispatch)
    {
        $eventName = $dispatch->getEventName();

        $this->assertEventCanBeHandled($eventName);

        return $this->make($dispatch->getEventName());
    }

    /**
     * @param $eventName
     *
     * @return bool
     */
    public function hasHandler($eventName)
    {
        return isset($this->registeredScenarios[$eventName]);
    }

    protected function getScenarios(LoaderInterface $scenarioLoader)
    {
        if (!is_array($loadedScenarios = $scenarioLoader->loadScenarios())) {
            throw new InvalidScenarioConfigurationException('Scenario registration list must be an array!');
        }

        $this->registeredScenarios = $loadedScenarios;
    }

    protected function make($eventName)
    {
        $scenarioClassName = $this->registeredScenarios[$eventName];

        return new $scenarioClassName;
    }

    protected function assertEventCanBeHandled($eventName)
    {
        if (!$this->hasHandler($eventName)) {
            throw new NoHandlerScenarioFoundException(
                sprintf('No handler scenario for event %s was registered.', $eventName)
            );
        }
    }

}