<?php namespace Keios\Apparatus\Tests;

use Mockery;
use Keios\Apparatus\Core\Event;

class TestEvent extends \PHPUnit_Framework_TestCase
{

    public function testEventSettersAndGetters()
    {
        $dispatcherMock = Mockery::mock('Keios\Apparatus\Core\Dispatcher');

        $event = new Event($dispatcherMock);

        $event->name('test.event')->with([1, 2, 3])->expect(['test.reaction']);

        $this->assertEquals('test.event', $event->getEventName());
        $this->assertEquals([1,2,3], $event->getEventData());
        $this->assertEquals(['test.reaction'], $event->getExpectedReactions());
    }

    public function testEventSendsItselfViaDispatcher()
    {
        $dispatcherMock = Mockery::mock('Keios\Apparatus\Core\Dispatcher');
        $dispatcherMock->shouldReceive('dispatch')->once()->with(Mockery::type('Keios\Apparatus\Core\Event'))->andReturn('reaction');

        $event = new Event($dispatcherMock);

        $reaction = $event->event('test.event')->with([1, 2, 3])->expect(['test.reaction'])->getReaction();

        $this->assertEquals('reaction', $reaction);
    }

    public function tearDown()
    {
        \Mockery::close();
    }
} 