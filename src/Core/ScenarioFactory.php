<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Exceptions\InvalidScenarioConfigurationException;
use Keios\Apparatus\Exceptions\NoHandlerScenarioFoundException;
use Keios\Apparatus\Exceptions\InvalidScenarioException;
use Keios\Apparatus\Contracts\LoaderInterface;
use Keios\Apparatus\Contracts\Dispatchable;

class ScenarioFactory
{
    protected $registeredScenarios;

    public function __construct(LoaderInterface $scenarioLoader)
    {
        $this->getScenarios($scenarioLoader);
    }

    public function findHandlerScenarioFor(Dispatchable $event)
    {
        $eventName = $event->getEventName();

        $this->assertEventCanBeHandled($eventName);

        return $this->make($event->getEventName());
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

        if (is_callable($scenarioClassName)) {
            return $this->resolveClosure($scenarioClassName);
        }

        return new $scenarioClassName;
    }

    protected function resolveClosure($closure)
    {
        $scenario = $closure();

        if ($interfaces = class_implements($scenario)) {
            if (in_array('Keios\Apparatus\Contracts\Runnable', $interfaces)) {
                return is_object($scenario) ? $scenario : new $scenario();
            }
        }

        throw new InvalidScenarioException(
            sprintf('Closure return value is not a valid scenario object or scenario class name.')
        );
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