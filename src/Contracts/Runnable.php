<?php namespace Keios\Apparatus\Contracts;

use Keios\Apparatus\Core\Dispatcher;

/**
 * Interface Runnable
 * @package Keios\Apparatus\Contracts
 */
interface Runnable
{
    /**
     * @param Dispatchable $event
     *
     * @return mixed
     */
    public function inject(Dispatchable $event);

    /**
     * @param Dispatcher $dispatcher
     *
     * @return mixed
     */
    public function setDispatcher(Dispatcher $dispatcher);

    /**
     * @return mixed
     */
    public function run();

    /**
     * @param string $eventName
     * @param        $data
     * @param array  $expectedReactions
     *
     * @return mixed
     */
    public function dispatch($eventName, $data, array $expectedReactions);
} 