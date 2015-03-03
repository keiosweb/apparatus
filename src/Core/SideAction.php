<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Runnable;

/**
 * Class SideAction
 *
 * @package Keios\Apparatus
 */
abstract class SideAction extends Action
{
    public function __invoke(Runnable $scenario, $result)
    {
        $this->scenario = $scenario;

        $this->execute($result);
    }
}