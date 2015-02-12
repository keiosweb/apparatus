<?php namespace Keios\Apparatus\Contracts;

use Keios\Apparatus\Core\Dispatcher;

interface Playable
{
    public function play($data, array $expectedReactions);

    public function setDispatcher(Dispatcher $dispatcher);
} 