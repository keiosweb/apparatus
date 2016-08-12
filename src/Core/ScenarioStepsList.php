<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Runnable;
use Keios\Apparatus\Exceptions\EventAlreadyRegisteredException;
use Keios\Apparatus\Exceptions\NoStepForEventFoundException;
use Keios\Apparatus\Exceptions\NoStepsDefinedException;
use Keios\Apparatus\Exceptions\NoStepWithNameFoundException;
use Keios\Apparatus\Exceptions\NotInitializedException;
use Keios\Apparatus\Exceptions\StepAlreadyRegisteredException;

/**
 * Class ScenarioStepsList
 *
 * @package Keios\Apparatus
 */
class ScenarioStepsList
{
    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * @var array
     */
    protected $eventBindings = [];

    /**
     * @var array
     */
    protected $steps = [];

    /**
     * @var null|Step
     */
    protected $currentStep;

    /**
     * @var \Keios\Apparatus\Contracts\Runnable
     */
    protected $scenario;

    /**
     * @param \Keios\Apparatus\Contracts\Runnable $scenario
     */
    public function __construct(Runnable $scenario)
    {
        $this->scenario = $scenario;
    }

    /**
     * @param $eventName
     *
     * @throws \Keios\Apparatus\Exceptions\NoStepsDefinedException
     * @throws \Keios\Apparatus\Exceptions\NoStepForEventFoundException
     */
    public function initialize($eventName)
    {
        $this->assertNotEmpty();

        $step = $this->getStepForEvent($eventName);

        $this->currentStep = $step->getName();

        $this->initialized = true;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->steps) === 0;
    }

    /**
     * @return mixed
     * @throws \Keios\Apparatus\Exceptions\NotInitializedException
     */
    public function getCurrentStep()
    {
        $this->assertIsInitialized();

        return $this->steps[$this->currentStep];
    }

    /**
     * @return \Keios\Apparatus\Core\Step
     * @throws \Keios\Apparatus\Exceptions\NotInitializedException
     */
    public function getNextStep()
    {
        $this->assertIsInitialized();

        reset($this->steps);
        while (key($this->steps) !== $this->currentStep && key($this->steps) !== null) {
            next($this->steps);
        }

        return next($this->steps);
    }

    /**
     * @return \Keios\Apparatus\Core\Step
     * @throws \Keios\Apparatus\Exceptions\NotInitializedException
     */
    public function getPreviousStep()
    {
        $this->assertIsInitialized();

        end($this->steps);
        while (key($this->steps) !== $this->currentStep && key($this->steps) !== null) {
            prev($this->steps);
        }

        return prev($this->steps);
    }

    /**
     * @return bool
     * @throws \Keios\Apparatus\Exceptions\NotInitializedException
     */
    public function hasNextStep()
    {
        return $this->getNextStep() !== false;
    }

    /**
     * @return bool
     * @throws \Keios\Apparatus\Exceptions\NotInitializedException
     */
    public function hasPreviousStep()
    {
        return $this->getPreviousStep() !== false;
    }

    /**
     * @throws \Keios\Apparatus\Exceptions\NotInitializedException
     */
    public function moveToNextStep()
    {
        $nextStep = $this->getNextStep() ?: $this->steps[$this->currentStep];
        $this->currentStep = $nextStep->getName();
    }

    /**
     * @throws \Keios\Apparatus\Exceptions\NotInitializedException
     */
    public function moveToPreviousStep()
    {
        $previousStep = $this->getPreviousStep() ?: $this->steps[$this->currentStep];
        $this->currentStep = $previousStep->getName();
    }

    /**
     * @param string $stepName
     *
     * @throws \Keios\Apparatus\Exceptions\NoStepWithNameFoundException
     */
    public function setCurrentStep($stepName)
    {
        $this->assertStepExists($stepName);
        $this->currentStep = $stepName;
    }

    /**
     * @param \Keios\Apparatus\Core\Step $step
     *
     * @throws \Keios\Apparatus\Exceptions\EventAlreadyRegisteredException
     * @throws \Keios\Apparatus\Exceptions\StepAlreadyRegisteredException
     */
    public function add(Step $step)
    {
        $this->assertUniqueEventBinding($step->getTriggeringEvents());
        $this->assertUniqueStepName($step->getName());

        $this->steps[$step->getName()] = $step;

        $this->registerTriggeringEvents($step);
    }

    /**
     * @param string $stepName
     *
     * @return bool
     */
    public function hasStepNamed($stepName)
    {
        return isset($this->steps[$stepName]);
    }

    /**
     * @param string $eventName
     *
     * @return mixed
     * @throws \Keios\Apparatus\Exceptions\NoStepForEventFoundException
     */
    public function getStepForEvent($eventName)
    {
        $this->assertHasStepForEvent($eventName);

        return $this->eventBindings[$eventName];
    }

    /**
     * @param string                     $stepName
     * @param \Keios\Apparatus\Core\Step $step
     *
     * @throws \Keios\Apparatus\Exceptions\EventAlreadyRegisteredException
     * @throws \Keios\Apparatus\Exceptions\NoStepWithNameFoundException
     */
    public function insertAfter($stepName, Step $step)
    {
        $this->insert($stepName, $step);
    }

    /**
     * @param string                     $stepName
     * @param \Keios\Apparatus\Core\Step $step
     *
     * @throws \Keios\Apparatus\Exceptions\EventAlreadyRegisteredException
     * @throws \Keios\Apparatus\Exceptions\NoStepWithNameFoundException
     */
    public function insertBefore($stepName, Step $step)
    {
        $this->insert($stepName, $step, false);
    }

    /**
     * @param string                     $stepNameToReplace
     * @param \Keios\Apparatus\Core\Step $replacingStep
     *
     * @throws \Keios\Apparatus\Exceptions\NoStepWithNameFoundException
     * @throws \Keios\Apparatus\Exceptions\EventAlreadyRegisteredException
     */
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

    /**
     * @param string                     $stepName
     * @param \Keios\Apparatus\Core\Step $step
     * @param bool                       $after
     *
     * @throws \Keios\Apparatus\Exceptions\EventAlreadyRegisteredException
     * @throws \Keios\Apparatus\Exceptions\NoStepWithNameFoundException
     */
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

    /**
     * @param \Keios\Apparatus\Core\Step $step
     */
    protected function registerTriggeringEvents(Step $step)
    {
        foreach ($step->getTriggeringEvents() as $triggeringEvent) {
            $this->eventBindings[$triggeringEvent] = $step;
        }
    }

    /**
     * @param array $eventNames
     *
     * @throws \Keios\Apparatus\Exceptions\EventAlreadyRegisteredException
     */
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

    /**
     * @param string $stepName
     *
     * @throws \Keios\Apparatus\Exceptions\StepAlreadyRegisteredException
     */
    protected function assertUniqueStepName($stepName)
    {
        if (isset($this->steps[$stepName])) {
            throw new StepAlreadyRegisteredException(
                sprintf('Step %s is already registered in scenario %s.', $stepName, get_class($this->scenario))
            );
        }
    }

    /**
     * @param string $eventName
     *
     * @throws \Keios\Apparatus\Exceptions\NoStepForEventFoundException
     */
    protected function assertHasStepForEvent($eventName)
    {
        if (!isset($this->eventBindings[$eventName])) {
            throw new NoStepForEventFoundException(
                sprintf('No step handling event %s in scenario %s.', $eventName, get_class($this->scenario))
            );
        }
    }

    /**
     * @throws \Keios\Apparatus\Exceptions\NoStepsDefinedException
     */
    protected function assertNotEmpty()
    {
        if ($this->isEmpty()) {
            throw new NoStepsDefinedException('Cannot initialize empty scenario.');
        }
    }

    /**
     * @throws \Keios\Apparatus\Exceptions\NotInitializedException
     */
    protected function assertIsInitialized()
    {
        if (!$this->initialized) {
            throw new NotInitializedException('Cannot reset pointer for uninitialized ScenarioStepsList.'); //internal
        }
    }

    /**
     * @param string $stepName
     *
     * @throws \Keios\Apparatus\Exceptions\NoStepWithNameFoundException
     */
    protected function assertStepExists($stepName)
    {
        if (!$this->hasStepNamed($stepName)) {
            throw new NoStepWithNameFoundException(
                sprintf('Could not find step named %s in scenario %s', $stepName, get_class($this->scenario))
            );
        }
    }
}