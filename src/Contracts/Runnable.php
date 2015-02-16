<?php namespace Keios\Apparatus\Contracts;

use Keios\Apparatus\Core\Dispatcher;
use Keios\Apparatus\Core\Step;

interface Runnable
{
    public function inject(Dispatchable $event);

    public function setDispatcher(Dispatcher $dispatcher);

    public function run();

    public function dispatch($eventName, $data, array $expectedReactions);
} 