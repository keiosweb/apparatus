<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Playable;

abstract class Scenario implements Playable
{
    protected $dispatcher;

    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    abstract function play($data, array $expectedReactions);

    protected function dispatch($code, $data, array $expectedReactions)
    {
        $dispatch = new Dispatch($this->dispatcher);
        return $dispatch->event($code)->with($data)->expect($expectedReactions)->getReaction();
    }
} 