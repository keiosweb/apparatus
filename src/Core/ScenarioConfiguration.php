<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Exceptions\ScenarioNotFoundException;
use Keios\Apparatus\Exceptions\InvalidScenarioException;
use Keios\Apparatus\Contracts\LoaderInterface;
use ReflectionClass;

/**
 * Class ScenarioConfiguration
 *
 * @package Keios\Apparatus
 */
class ScenarioConfiguration implements LoaderInterface
{
    /**
     * @var array
     */
    protected $registeredScenarios = [];

    /**
     * @param string $eventName
     * @param string $scenarioClassName
     *
     * @throws \Keios\Apparatus\Exceptions\InvalidScenarioException
     * @throws \Keios\Apparatus\Exceptions\ScenarioNotFoundException
     */
    public function bind($eventName, $scenarioClassName)
    {
        if (!is_callable($scenarioClassName)) {
            $this->assertScenarioExists($scenarioClassName);
            $this->assertScenarioIsRunnable($scenarioClassName);
        }

        // todo solve multiple event bindings problem
        // actual solution is to rebind, latest binding wins

        $this->registeredScenarios[$eventName] = $scenarioClassName;
    }

    /**
     * @param string $scenarioClassName
     *
     * @throws \Keios\Apparatus\Exceptions\ScenarioNotFoundException
     */
    protected function assertScenarioExists($scenarioClassName)
    {
        if (!class_exists($scenarioClassName)) {
            throw new ScenarioNotFoundException(
                sprintf('Scenario class %s was not found or does not exist.', $scenarioClassName)
            );
        }
    }

    /**
     * @param string $scenarioClassName
     *
     * @throws \Keios\Apparatus\Exceptions\InvalidScenarioException
     */
    protected function assertScenarioIsRunnable($scenarioClassName)
    {
        $class = new ReflectionClass($scenarioClassName);
        if (!$class->implementsInterface('Keios\Apparatus\Contracts\Runnable')) {
            throw new InvalidScenarioException(
                sprintf(
                    'Scenario class %s does not implement interface Keios\Apparatus\Contracts\Runnable.',
                    $scenarioClassName
                )
            );
        }
    }

    /**
     * @return array
     */
    public function loadScenarios()
    {
        return $this->registeredScenarios;
    }
}