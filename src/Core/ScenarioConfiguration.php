<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Exceptions\ScenarioNotFoundException;
use Keios\Apparatus\Exceptions\InvalidScenarioException;
use Keios\Apparatus\Contracts\LoaderInterface;
use ReflectionClass;

class ScenarioConfiguration implements LoaderInterface
{
    protected $registeredScenarios = [];

    public function bind($eventName, $scenarioClassName)
    {
        $this->assertScenarioExists($scenarioClassName);
        $this->assertScenarioIsPlayable($scenarioClassName);
        $this->registeredScenarios[$eventName] = $scenarioClassName;
    }

    protected function assertScenarioExists($scenarioClassName)
    {
        if (!class_exists($scenarioClassName)) {
            throw new ScenarioNotFoundException(
                sprintf('Scenario class %s was not found or does not exist.', $scenarioClassName)
            );
        }
    }

    protected function assertScenarioIsPlayable($scenarioClassName)
    {
        $class = new ReflectionClass($scenarioClassName);
        if (!$class->implementsInterface('Keios\Apparatus\Contracts\Playable')) {
            throw new InvalidScenarioException(
                sprintf(
                    'Scenario class %s does not implement interface Keios\Apparatus\Contracts\Playable.',
                    $scenarioClassName
                )
            );
        }
    }

    public function loadScenarios()
    {
        return $this->registeredScenarios;
    }
}