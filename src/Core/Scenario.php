<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Dispatchable;
use Keios\Apparatus\Contracts\Runnable;

abstract class Scenario implements Runnable
{
    protected $dispatcher;

    protected $steps;

    protected $eventName;

    protected $eventData;

    protected $expectedReactions;

    protected $returnAfterCurrentStep = false;

    protected $results = [];

    protected static $registeredCallbacks = [];

    public function __construct()
    {
        $this->steps = new ScenarioStepsList($this);
    }

    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

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

    public function run()
    {
        $this->bootScenario();

        $this->steps->initialize($this->eventName);

        do {
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

    public function dispatch($eventName, $data, array $expectedReactions)
    {
        $event = new Event($this->dispatcher);

        return $event->name($eventName)->with($data)->expect($expectedReactions)->getReaction();
    }

    public function add(Step $step)
    {
        $this->steps->add($step);
    }

    public function replace($stepName, Step $step)
    {
        $this->steps->replace($stepName, $step);
    }

    public function insertBefore($stepName, Step $step)
    {
        $this->steps->insertBefore($stepName, $step);
    }

    public function insertAfter($stepName, Step $step)
    {
        $this->steps->insertAfter($stepName, $step);
    }

    public function getResultFrom($stepName)
    {
        return $this->results[$stepName];
    }

    public function getLastResult()
    {
        return end($this->results);
    }

    public function stopAndReturn()
    {
        $this->returnAfterCurrentStep = true;
    }

    protected function bootScenario()
    {
        $this->setUp();
        $this->bootExtensibility();
        $this->results[] = $this->eventData;
    }

    protected function bootExtensibility()
    {
        foreach (static::$registeredCallbacks as $callback) {
            $callback($this);
        }
    }

    protected abstract function setUp();

    public static function extend(callable $callback)
    {
        static::$registeredCallbacks[] = $callback;
    }

} 