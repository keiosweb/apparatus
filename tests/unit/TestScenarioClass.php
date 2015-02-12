<?php namespace Keios\Apparatus\Tests;

use Keios\Apparatus\Tests\Fixtures\TestScenario;
use Mockery;

class TestScenarioClass extends \PHPUnit_Framework_TestCase
{
    public function testScenarioCanDispatchEventsAndGetReactions()
    {
        $dispatcherMock = Mockery::mock('Keios\Apparatus\Core\Dispatcher');
        $dispatcherMock->shouldReceive('dispatch')->with(Mockery::type('Keios\Apparatus\Core\Dispatch'));

        $scenario = new TestScenario();
        $scenario->setDispatcher($dispatcherMock);
        $scenario->testDispatchingNewEvents();
    }

    public function tearDown()
    {
        \Mockery::close();
    }
} 