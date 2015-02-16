<?php namespace Keios\Apparatus\Tests\Fixtures;

use Keios\Apparatus\Core\Action;

class ASimpleTestAction extends Action
{
    public function execute()
    {
        return 'response';
    }
}