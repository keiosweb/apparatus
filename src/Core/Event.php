<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Dispatchable;

/**
 * Class Event
 *
 * @package Keios\Apparatus
 */
class Event implements Dispatchable
{
    /**
     * @var \Keios\Apparatus\Core\Dispatcher
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $eventName;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var array
     */
    protected $expectedReactions;

    /**
     * @param \Keios\Apparatus\Core\Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @fluent
     *
     * @param string $eventName
     *
     * @return \Keios\Apparatus\Contracts\Dispatchable $this
     */
    public function name($eventName)
    {
        $this->eventName = $eventName;

        return $this;
    }

    /**
     * @fluent
     *
     * @param string $eventName
     *
     * @return \Keios\Apparatus\Contracts\Dispatchable $this
     */
    public function event($eventName)
    {
        return $this->name($eventName);
    }

    /**
     * @fluent
     *
     * @param $data
     *
     * @return \Keios\Apparatus\Contracts\Dispatchable $this
     */
    public function with($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @fluent
     *
     * @param array $expectedReactions
     *
     * @return \Keios\Apparatus\Contracts\Dispatchable $this
     */
    public function expect(array $expectedReactions)
    {
        // todo validate?
        $this->expectedReactions = $expectedReactions;

        return $this;
    }

    /**
     * @return mixed
     * @throws \Keios\Apparatus\Exceptions\NoHandlerScenarioFoundException
     */
    public function getReaction()
    {
        // todo self state validation?
        return $this->dispatcher->dispatch($this);
    }

    /**
     * @return string|null
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * @return mixed
     */
    public function getEventData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getExpectedReactions()
    {
        return $this->expectedReactions;
    }
} 