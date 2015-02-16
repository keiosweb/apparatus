<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Runnable;

abstract class Action
{
    protected $scenario;

    abstract public function execute();

    public function __invoke(Runnable $scenario, $lastStepResult)
    {
        $this->scenario = $scenario;

        return $this->execute($lastStepResult);
    }
}