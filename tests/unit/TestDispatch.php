<?php namespace Keios\Apparatus\Tests;

use Mockery;
use Keios\Apparatus\Core\Dispatch;

class TestDispatch extends \PHPUnit_Framework_TestCase
{

    public function testDispatchSettersAndGetters()
    {
        $dispatcherMock = Mockery::mock('Keios\Apparatus\Core\Dispatcher');

        $dispatch = new Dispatch($dispatcherMock);

        $dispatch->event('test.event')->with([1, 2, 3])->expect(['test.reaction']);

        $this->assertEquals('test.event', $dispatch->getEventName());
        $this->assertEquals([1,2,3], $dispatch->getEventData());
        $this->assertEquals(['test.reaction'], $dispatch->getExpectedReactions());
    }

    public function testDispatchSendsItselfViaDispatcher()
    {
        $dispatcherMock = Mockery::mock('Keios\Apparatus\Core\Dispatcher');
        $dispatcherMock->shouldReceive('dispatch')->once()->with(Mockery::type('Keios\Apparatus\Core\Dispatch'))->andReturn('reaction');

        $dispatch = new Dispatch($dispatcherMock);

        $reaction = $dispatch->event('test.event')->with([1, 2, 3])->expect(['test.reaction'])->getReaction();

        $this->assertEquals('reaction', $reaction);
    }

    public function tearDown()
    {
        \Mockery::close();
    }
} 