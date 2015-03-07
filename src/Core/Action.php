<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Runnable;

/**
 * Class Action
 *
 * @package Keios\Apparatus
 */
abstract class Action
{
    /**
     * @var \Keios\Apparatus\Contracts\Runnable $scenario
     */
    protected $scenario;

    /**
     * @param $result
     *
     * @return mixed
     */
    abstract public function execute($result);

    /**
     * @param \Keios\Apparatus\Contracts\Runnable $scenario
     * @param                                     $lastStepResult
     *
     * @return mixed
     */
    public function __invoke(Runnable $scenario, $lastStepResult)
    {
        $this->scenario = $scenario;

        return $this->execute($lastStepResult);
    }
}