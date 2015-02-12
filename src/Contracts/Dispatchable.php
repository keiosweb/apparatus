<?php namespace Keios\Apparatus\Contracts;


interface Dispatchable
{

    /**
     * @fluent
     * @param $eventName
     *
     * @return Dispatchable
     */
    public function event($eventName);

    /**
     * @fluent
     * @param $data
     *
     * @return Dispatchable
     */
    public function with($data);

    /**
     * @fluent
     * @param array $expectedReactions
     *
     * @return Dispatchable
     */
    public function expect(array $expectedReactions);

    /**
     * @return mixed
     */
    public function getReaction();

    /**
     * @return string
     */
    public function getEventName();

    /**
     * @return mixed
     */
    public function getEventData();

    /**
     * @return array
     */
    public function getExpectedReactions();
}