<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Exceptions\InvalidScenarioConfigurationException;
use Keios\Apparatus\Exceptions\NoHandlerScenarioFoundException;
use Keios\Apparatus\Exceptions\InvalidScenarioException;
use Keios\Apparatus\Contracts\LoaderInterface;
use Keios\Apparatus\Contracts\Dispatchable;

/**
 * Class ScenarioFactory
 *
 * @package Keios\Apparatus
 */
class ScenarioFactory
{
    /**
     * @var
     */
    protected $registeredScenarios;

    /**
     * @param \Keios\Apparatus\Contracts\LoaderInterface $scenarioLoader
     *
     * @throws \Keios\Apparatus\Exceptions\InvalidScenarioConfigurationException
     */
    public function __construct(LoaderInterface $scenarioLoader)
    {
        $this->getScenariosFrom($scenarioLoader);
    }

    /**
     * @param \Keios\Apparatus\Contracts\Dispatchable $event
     *
     * @return mixed
     * @throws \Keios\Apparatus\Exceptions\InvalidScenarioException
     * @throws \Keios\Apparatus\Exceptions\NoHandlerScenarioFoundException
     */
    public function findHandlerScenarioFor(Dispatchable $event)
    {
        $eventName = $event->getEventName();

        $this->assertEventCanBeHandled($eventName);

        return $this->make($event->getEventName());
    }

    /**
     * @param string $eventName
     *
     * @return bool
     */
    public function hasHandler($eventName)
    {
        return isset($this->registeredScenarios[$eventName]);
    }

    /**
     * @param \Keios\Apparatus\Contracts\LoaderInterface $scenarioLoader
     *
     * @throws \Keios\Apparatus\Exceptions\InvalidScenarioConfigurationException
     */
    protected function getScenariosFrom(LoaderInterface $scenarioLoader)
    {
        if (!is_array($loadedScenarios = $scenarioLoader->loadScenarios())) {
            throw new InvalidScenarioConfigurationException('Scenario registration list must be an array!');
        }

        $this->registeredScenarios = $loadedScenarios;
    }

    /**
     * @param $eventName
     *
     * @return mixed
     * @throws \Keios\Apparatus\Exceptions\InvalidScenarioException
     */
    protected function make($eventName)
    {
        $scenarioClassName = $this->registeredScenarios[$eventName];

        if (is_callable($scenarioClassName)) {
            return $this->resolveClosure($scenarioClassName);
        }

        return new $scenarioClassName;
    }

    /**
     * @param $closure
     *
     * @return mixed
     * @throws \Keios\Apparatus\Exceptions\InvalidScenarioException
     */
    protected function resolveClosure($closure)
    {
        $scenario = $closure();
        $interfaces = class_implements($scenario);
        if ($interfaces && in_array('Keios\Apparatus\Contracts\Runnable', $interfaces, true)) {
                return is_object($scenario) ? $scenario : new $scenario();
        }

        throw new InvalidScenarioException(
            sprintf('Closure return value is not a valid scenario object or scenario class name.')
        );
    }

    /**
     * @param string $eventName
     *
     * @throws \Keios\Apparatus\Exceptions\NoHandlerScenarioFoundException
     */
    protected function assertEventCanBeHandled($eventName)
    {
        if (!$this->hasHandler($eventName)) {
            throw new NoHandlerScenarioFoundException(
                sprintf('No handler scenario for event %s was registered.', $eventName)
            );
        }
    }

}