<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Runnable;
use Keios\Apparatus\Exceptions\EventAlreadyRegisteredException;
use Keios\Apparatus\Exceptions\NoStepForEventFoundException;
use Keios\Apparatus\Exceptions\NoStepsDefinedException;
use Keios\Apparatus\Exceptions\NoStepWithNameFoundException;
use Keios\Apparatus\Exceptions\NotInitializedException;
use Keios\Apparatus\Exceptions\StepAlreadyRegisteredException;

class ScenarioStepsList
{
    protected $initialized = false;

    protected $eventBindings = [];

    protected $steps = [];

    protected $currentStep = null;

    protected $scenario;

    public function __construct(Runnable $scenario)
    {
        $this->scenario = $scenario;
    }

    public function initialize($eventName)
    {
        $this->assertNotEmpty();

        $step = $this->getStepForEvent($eventName);

        $this->currentStep = $step->getName();

        $this->initialized = true;
    }

    public function isEmpty()
    {
        return count($this->steps) === 0;
    }

    public function getCurrentStep()
    {
        $this->assertIsInitialized();

        return $this->steps[$this->currentStep];
    }

    public function getNextStep()
    {
        $this->assertIsInitialized();

        reset($this->steps);
        while (key($this->steps) !== $this->currentStep && key($this->steps) !== null) {
            next($this->steps);
        }

        return next($this->steps);
    }

    public function getPreviousStep()
    {
        $this->assertIsInitialized();

        end($this->steps);
        while (key($this->steps) !== $this->currentStep && key($this->steps) !== null) {
            prev($this->steps);
        }

        return prev($this->steps);
    }

    public function hasNextStep()
    {
        return $this->getNextStep() !== false;
    }

    public function hasPreviousStep()
    {
        return $this->getPreviousStep() !== false;
    }

    public function moveToNextStep()
    {
        $nextStep = $this->getNextStep() ?: $this->steps[$this->currentStep];
        $this->currentStep = $nextStep->getName();
    }

    public function moveToPreviousStep()
    {
        $previousStep = $this->getPreviousStep() ?: $this->steps[$this->currentStep];
        $this->currentStep = $previousStep->getName();
    }

    public function setCurrentStep($stepName)
    {
        $this->assertStepExists($stepName);
        $this->currentStep = $stepName;
    }

    public function add(Step $step)
    {
        $this->assertUniqueEventBinding($step->getTriggeringEvents());
        $this->assertUniqueStepName($step->getName());

        $this->steps[$step->getName()] = $step;

        $this->registerTriggeringEvents($step);
    }

    public function hasStepNamed($stepName)
    {
        return isset($this->steps[$stepName]);
    }

    public function getStepForEvent($eventName)
    {
        $this->assertHasStepForEvent($eventName);

        return $this->eventBindings[$eventName];
    }

    public function insertAfter($stepName, Step $step)
    {
        $this->insert($stepName, $step);
    }

    public function insertBefore($stepName, Step $step)
    {
        $this->insert($stepName, $step, false);
    }

    public function replace($stepNameToReplace, Step $replacingStep)
    {
        $this->assertStepExists($stepNameToReplace);

        // remove event bindings for step being replaced
        $stepToReplace = $this->steps[$stepNameToReplace];
        foreach ($stepToReplace->getTriggeringEvents() as $eventName) {
            unset($this->eventBindings[$eventName]);
        }

        // insert after the step we want to replace
        $this->insert($stepNameToReplace, $replacingStep, true);

        // remove step
        unset($this->steps[$stepNameToReplace]);

        // reindex steps array
        $temporaryStepNames = array_keys($this->steps);
        $temporarySteps = array_values($this->steps);

        $this->steps = array_combine($temporaryStepNames, $temporarySteps);
    }

    protected function insert($stepName, Step $step, $after = true)
    {
        $this->assertStepExists($stepName);

        $this->assertUniqueEventBinding($step->getTriggeringEvents());

        $names = array_keys($this->steps);
        $steps = array_values($this->steps);

        $insertPosition = array_search($stepName, $names) + ($after ? 1 : 0);

        $namesAfterPosition = array_splice($names, $insertPosition);
        $stepsAfterPosition = array_splice($steps, $insertPosition);

        $names[] = $step->getName();
        $steps[] = $step;

        $this->steps = array_merge(
            array_combine($names, $steps),
            array_combine($namesAfterPosition, $stepsAfterPosition)
        );

        $this->registerTriggeringEvents($step);
    }

    protected function registerTriggeringEvents(Step $step)
    {
        foreach ($step->getTriggeringEvents() as $triggeringEvent) {
            $this->eventBindings[$triggeringEvent] = $step;
        }
    }

    protected function assertUniqueEventBinding(array $eventNames)
    {
        foreach ($eventNames as $eventName) {
            if (isset($this->eventBindings[$eventName])) {
                throw new EventAlreadyRegisteredException(
                    sprintf(
                        'Event %s is already handled in scenario %s with step %s.',
                        $eventName,
                        get_class($this->scenario),
                        $this->eventBindings[$eventName]->getName()
                    )
                );
            }
        }
    }

    protected function assertUniqueStepName($stepName)
    {
        if (isset($this->steps[$stepName])) {
            throw new StepAlreadyRegisteredException(
                sprintf('Step %s is already registered in scenario %s.', $stepName, get_class($this->scenario))
            );
        }
    }

    protected function assertHasStepForEvent($eventName)
    {
        if (!isset($this->eventBindings[$eventName])) {
            throw new NoStepForEventFoundException(
                sprintf('No step handling event %s in scenario %s.', $eventName, get_class($this->scenario))
            );
        }
    }

    protected function assertNotEmpty()
    {
        if ($this->isEmpty()) {
            throw new NoStepsDefinedException('Cannot initialize empty scenario.');
        }
    }

    protected function assertIsInitialized()
    {
        if (!$this->initialized) {
            throw new NotInitializedException('Cannot reset pointer for uninitialized ScenarioStepsList.'); //internal
        }
    }

    private function assertStepExists($stepName)
    {
        if (!$this->hasStepNamed($stepName)) {
            throw new NoStepWithNameFoundException(
                sprintf('Could not find step named %s in scenario %s', $stepName, get_class($this->scenario))
            );
        }
    }
}