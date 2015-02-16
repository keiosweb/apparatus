<?php namespace Keios\Apparatus\Tests\Fixtures;

use Keios\Apparatus\Core\Action;

class DispatchingTestEventAction extends Action
{
    public function execute()
    {
        return $this->scenario->dispatch('test.event', [1, 2, 3], ['some', 'reactions']);
    }

}