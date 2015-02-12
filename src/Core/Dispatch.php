<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Dispatchable;

class Dispatch implements Dispatchable
{
    protected $dispatcher;

    protected $eventName;

    protected $data;

    protected $expectedReactions;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @fluent
     *
     * @param $eventName
     *
     * @return \Keios\Apparatus\Contracts\Dispatchable $this
     */
    public function event($eventName)
    {
        $this->eventName = $eventName;

        return $this;
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

    public function getReaction()
    {
        // todo self state validation?
        return $this->dispatcher->dispatch($this);
    }

    public function getEventName()
    {
        return $this->eventName;
    }

    public function getEventData()
    {
        return $this->data;
    }

    public function getExpectedReactions()
    {
        return $this->expectedReactions;
    }
} 