<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Dispatchable;
use Keios\Apparatus\Contracts\Runnable;

/**
 * Class Scenario
 *
 * @package Keios\Apparatus\Core
 */
abstract class Scenario implements Runnable
{
    /**
     * @var \Keios\Apparatus\Core\Dispatcher
     */
    protected $dispatcher;

    /**
     * @var \Keios\Apparatus\Core\ScenarioStepsList
     */
    protected $steps;

    /**
     * @var string
     */
    protected $eventName;

    /**
     * @var mixed
     */
    protected $eventData;

    /**
     * @var array
     */
    protected $expectedReactions;

    /**
     * @var bool
     */
    protected $returnAfterCurrentStep = false;

    /**
     * @var array
     */
    protected $results = [];

    /**
     * @var bool
     */
    protected $booted = false;

    /**
     * @var array
     */
    protected static $registeredCallbacks = [];

    /**
     * @constructor
     */
    public function __construct()
    {
        $this->steps = new ScenarioStepsList($this);
    }

    /**
     * @param \Keios\Apparatus\Core\Dispatcher $dispatcher
     *
     * @return mixed|void
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param \Keios\Apparatus\Contracts\Dispatchable $event
     *
     * @return mixed|void
     */
    public function inject(Dispatchable $event)
    {
        $this->eventData = $event->getEventData();
        $this->eventName = $event->getEventName();
        $this->expectedReactions = $event->getExpectedReactions();
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * @return string
     */
    public function getEventData()
    {
        return $this->eventData;
    }

    /**
     * @return array
     */
    public function getExpectedReactions()
    {
        return $this->expectedReactions;
    }

    /**
     * @param string $property
     *
     * @return bool
     */
    public function has($property)
    {
        return property_exists($this, $property);
    }

    /**
     * @return mixed
     * @throws \Keios\Apparatus\Exceptions\NoStepForEventFoundException
     * @throws \Keios\Apparatus\Exceptions\NotInitializedException
     * @throws \Keios\Apparatus\Exceptions\NoStepsDefinedException
     */
    public function run()
    {
        $this->bootScenario();

        $this->steps->initialize($this->eventName);

        do {
            /**
             * @var \Keios\Apparatus\Core\Step $currentStep
             */
            $currentStep = $this->steps->getCurrentStep();

            $this->results[$currentStep->getName()] = $currentStep($this->getLastResult());

            if ($this->steps->hasNextStep()) {
                $this->steps->moveToNextStep();
            } else {
                break;
            }
        } while ($this->returnAfterCurrentStep === false);

        return $this->getLastResult();
    }

    /**
     * @param string $eventName
     * @param        $data
     * @param array  $expectedReactions
     *
     * @return mixed
     */
    public function dispatch($eventName, $data, array $expectedReactions)
    {
        $event = new Event($this->dispatcher);

        return $event->name($eventName)->with($data)->expect($expectedReactions)->getReaction();
    }

    /**
     * @param \Keios\Apparatus\Core\Step $step
     *
     * @throws \Keios\Apparatus\Exceptions\EventAlreadyRegisteredException
     * @throws \Keios\Apparatus\Exceptions\StepAlreadyRegisteredException
     */
    public function add(Step $step)
    {
        $step->setScenario($this);
        $this->steps->add($step);
    }

    /**
     * @param                            $stepName
     * @param \Keios\Apparatus\Core\Step $step
     *
     * @throws \Keios\Apparatus\Exceptions\NoStepWithNameFoundException
     * @throws \Keios\Apparatus\Exceptions\EventAlreadyRegisteredException
     */
    public function replace($stepName, Step $step)
    {
        $this->steps->replace($stepName, $step);
    }

    /**
     * @param                            $stepName
     * @param \Keios\Apparatus\Core\Step $step
     *
     * @throws \Keios\Apparatus\Exceptions\EventAlreadyRegisteredException
     * @throws \Keios\Apparatus\Exceptions\NoStepWithNameFoundException
     */
    public function insertBefore($stepName, Step $step)
    {
        $this->steps->insertBefore($stepName, $step);
    }

    /**
     * @param                            $stepName
     * @param \Keios\Apparatus\Core\Step $step
     *
     * @throws \Keios\Apparatus\Exceptions\EventAlreadyRegisteredException
     * @throws \Keios\Apparatus\Exceptions\NoStepWithNameFoundException
     */
    public function insertAfter($stepName, Step $step)
    {
        $this->steps->insertAfter($stepName, $step);
    }

    /**
     * @param $stepName
     *
     * @return mixed
     */
    public function getResultFrom($stepName)
    {
        return $this->results[$stepName];
    }

    /**
     * @return mixed
     */
    public function getLastResult()
    {
        return end($this->results);
    }

    /**
     * Toggles return after current step
     */
    public function waitForInteraction()
    {
        $this->returnAfterCurrentStep = true;
    }

    /**
     * Boots scenario
     */
    protected function bootScenario()
    {
        $this->returnAfterCurrentStep = false;
        $this->results = [];
        if (!$this->booted) {
            $this->setUp();
            $this->bootExtensibility();
            $this->booted = true;
        }
        $this->results[] = $this->eventData;
    }

    /**
     *
     */
    protected function bootExtensibility()
    {
        foreach (static::$registeredCallbacks as $callback) {
            $callback($this);
        }
    }

    /**
     * @return mixed
     */
    protected abstract function setUp();

    /**
     * @param callable $callback
     */
    public static function extend(callable $callback)
    {
        static::$registeredCallbacks[] = $callback;
    }

    /**
     * Flushes extendable callback cache
     */
    public static function flush()
    {
        static::$registeredCallbacks = [];
    }

} 