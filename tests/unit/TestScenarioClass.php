<?php namespace Keios\Apparatus\Tests;

use Keios\Apparatus\Tests\Fixtures\BasicScenario;
use Mockery;

class TestScenarioClass extends \PHPUnit_Framework_TestCase
{
    protected $dispatcherMock;

    public function setUp()
    {
        $this->dispatcherMock = Mockery::mock('Keios\Apparatus\Core\Dispatcher');
        $eventMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');

        $eventMock->shouldReceive('getEventName')->andReturn('test.event');
        $eventMock->shouldReceive('getExpectedReactions')->andReturn(['some reaction']);
        $eventMock->shouldReceive('getEventData')->andReturn([1, 2, 3]);
    }

    public function testEventParametersGetters()
    {
        $eventMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');

        $eventMock->shouldReceive('getEventName')->andReturn('test.event');
        $eventMock->shouldReceive('getExpectedReactions')->andReturn(['some reaction']);
        $eventMock->shouldReceive('getEventData')->andReturn([1, 2, 3]);

        $scenario = new BasicScenario();
        $scenario->setDispatcher($this->dispatcherMock);
        $scenario->inject($eventMock);

        $this->assertEquals('test.event', $scenario->getEventName());
        $this->assertEquals(['some reaction'], $scenario->getExpectedReactions());
        $this->assertEquals([1,2,3], $scenario->getEventData());
    }

    public function testScenarioCanExecuteChainsOfStepsPassingReturnValuesToEach()
    {
        $eventMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');

        $eventMock->shouldReceive('getEventName')->andReturn('test.event');
        $eventMock->shouldReceive('getExpectedReactions')->andReturn(['some reaction']);
        $eventMock->shouldReceive('getEventData')->andReturn([1, 2, 3]);

        $step1Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $step2Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $step3Mock = Mockery::mock('Keios\Apparatus\Core\Step');

        $step1Mock->shouldReceive('setScenario')->with(Mockery::type('Keios\Apparatus\Contracts\Runnable'));
        $step2Mock->shouldReceive('setScenario')->with(Mockery::type('Keios\Apparatus\Contracts\Runnable'));
        $step3Mock->shouldReceive('setScenario')->with(Mockery::type('Keios\Apparatus\Contracts\Runnable'));

        $step1Mock->shouldReceive('getName')->andReturn('step1');
        $step2Mock->shouldReceive('getName')->andReturn('step2');
        $step3Mock->shouldReceive('getName')->andReturn('step3');

        $step1Mock->shouldReceive('getTriggeringEvents')->andReturn(['test.event']);
        $step2Mock->shouldReceive('getTriggeringEvents')->andReturn([]);
        $step3Mock->shouldReceive('getTriggeringEvents')->andReturn([]);

        $step1Mock->shouldReceive('__invoke')
            ->with([1, 2, 3])
            ->andReturn('step 1 result');
        $step2Mock->shouldReceive('__invoke')
            ->with('step 1 result')
            ->andReturn('step 2 result');
        $step3Mock->shouldReceive('__invoke')
            ->with('step 2 result')
            ->andReturn('step 3 result final');

        $scenario = new BasicScenario();
        $scenario->setDispatcher($this->dispatcherMock);
        $scenario->inject($eventMock);
        $scenario->add($step1Mock);
        $scenario->add($step2Mock);
        $scenario->add($step3Mock);
        $result = $scenario->run();

        $step3Mock->shouldHaveReceived('__invoke');
        $this->assertEquals('step 3 result final', $result);
    }

    public function testScenarioCanExecuteChainsOfEventBoundStepsWithBreaksBetweenThem()
    {
        $eventMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');

        $eventMock->shouldReceive('getEventName')->andReturn('test.event');
        $eventMock->shouldReceive('getExpectedReactions')->andReturn(['some reaction']);
        $eventMock->shouldReceive('getEventData')->andReturn([1, 2, 3]);

        $step1Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $step2Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $step3Mock = Mockery::mock('Keios\Apparatus\Core\Step');

        $step1Mock->shouldReceive('setScenario')->with(Mockery::type('Keios\Apparatus\Contracts\Runnable'));
        $step2Mock->shouldReceive('setScenario')->with(Mockery::type('Keios\Apparatus\Contracts\Runnable'));
        $step3Mock->shouldReceive('setScenario')->with(Mockery::type('Keios\Apparatus\Contracts\Runnable'));

        $step1Mock->shouldReceive('getName')->andReturn('step1');
        $step2Mock->shouldReceive('getName')->andReturn('step2');
        $step3Mock->shouldReceive('getName')->andReturn('step3');

        $step1Mock->shouldReceive('getTriggeringEvents')->andReturn(['test.event']);
        $step2Mock->shouldReceive('getTriggeringEvents')->andReturn(['test.event.2']);
        $step3Mock->shouldReceive('getTriggeringEvents')->andReturn(['test.event.3']);

        $secondEventMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');
        $secondEventMock->shouldReceive('getEventName')->andReturn('test.event.2');
        $secondEventMock->shouldReceive('getExpectedReactions')->andReturn(['some reaction']);
        $secondEventMock->shouldReceive('getEventData')->andReturn([1, 2, 3]);

        $thirdEventMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');
        $thirdEventMock->shouldReceive('getEventName')->andReturn('test.event.3');
        $thirdEventMock->shouldReceive('getExpectedReactions')->andReturn(['some reaction']);
        $thirdEventMock->shouldReceive('getEventData')->andReturn([1, 2, 3]);

        $scenarioFirstInstance = new BasicScenario();
        $scenarioSecondInstance = new BasicScenario();
        $scenarioThirdInstance = new BasicScenario();

        $step1Mock->shouldReceive('__invoke')
            ->with([1, 2, 3])
            ->andReturnUsing(
                function () use ($scenarioFirstInstance) {
                    $scenarioFirstInstance->waitForInteraction();

                    return 'step 1 result';
                }
            );

        $step2Mock->shouldReceive('__invoke')
            ->with([1, 2, 3])
            ->andReturnUsing(
                function () use ($scenarioSecondInstance) {
                    $scenarioSecondInstance->waitForInteraction();

                    return 'step 2 result';
                }
            );

        $step3Mock->shouldReceive('__invoke')
            ->with([1, 2, 3])
            ->andReturnUsing(
                function () use ($scenarioThirdInstance) {
                    $scenarioThirdInstance->waitForInteraction();

                    return 'step 3 result final';
                }
            );


        $scenarioFirstInstance->setDispatcher($this->dispatcherMock);
        $scenarioFirstInstance->inject($eventMock);
        $scenarioFirstInstance->add($step1Mock);
        $result1 = $scenarioFirstInstance->run();

        $scenarioSecondInstance->setDispatcher($this->dispatcherMock);
        $scenarioSecondInstance->inject($secondEventMock);
        $scenarioSecondInstance->add($step2Mock);
        $result2 = $scenarioSecondInstance->run();

        $scenarioThirdInstance->setDispatcher($this->dispatcherMock);
        $scenarioThirdInstance->inject($thirdEventMock);
        $scenarioThirdInstance->add($step3Mock);
        $result3 = $scenarioThirdInstance->run();

        $step3Mock->shouldHaveReceived('__invoke');
        $this->assertEquals('step 1 result', $result1);
        $this->assertEquals('step 2 result', $result2);
        $this->assertEquals('step 3 result final', $result3);
    }

    public function testCanInsertAndReplaceStepsInScenario()
    {
        $eventMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');

        $eventMock->shouldReceive('getEventName')->andReturn('test.event');
        $eventMock->shouldReceive('getExpectedReactions')->andReturn(['some reaction']);
        $eventMock->shouldReceive('getEventData')->andReturn([1, 2, 3]);

        $step1Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $step2Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $step3Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $step4Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $step5Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $step6Mock = Mockery::mock('Keios\Apparatus\Core\Step');

        $step1Mock->shouldReceive('setScenario')->with(Mockery::type('Keios\Apparatus\Contracts\Runnable'));
        $step2Mock->shouldReceive('setScenario')->with(Mockery::type('Keios\Apparatus\Contracts\Runnable'));
        $step3Mock->shouldReceive('setScenario')->with(Mockery::type('Keios\Apparatus\Contracts\Runnable'));
        $step4Mock->shouldReceive('setScenario')->with(Mockery::type('Keios\Apparatus\Contracts\Runnable'));
        $step5Mock->shouldReceive('setScenario')->with(Mockery::type('Keios\Apparatus\Contracts\Runnable'));
        $step6Mock->shouldReceive('setScenario')->with(Mockery::type('Keios\Apparatus\Contracts\Runnable'));

        $step1Mock->shouldReceive('getName')->andReturn('step1');
        $step2Mock->shouldReceive('getName')->andReturn('step2');
        $step3Mock->shouldReceive('getName')->andReturn('step3');
        $step4Mock->shouldReceive('getName')->andReturn('step4');
        $step5Mock->shouldReceive('getName')->andReturn('step5');
        $step6Mock->shouldReceive('getName')->andReturn('step6');

        $step1Mock->shouldReceive('getTriggeringEvents')->andReturn(['test.event']);
        $step2Mock->shouldReceive('getTriggeringEvents')->andReturn([]);
        $step3Mock->shouldReceive('getTriggeringEvents')->andReturn([]);
        $step4Mock->shouldReceive('getTriggeringEvents')->andReturn([]);
        $step5Mock->shouldReceive('getTriggeringEvents')->andReturn([]);
        $step6Mock->shouldReceive('getTriggeringEvents')->andReturn([]);

        $scenario = new BasicScenario();

        $step1Mock->shouldReceive('__invoke')
            ->with([1, 2, 3])
            ->andReturn('step 1 result');

        $step3Mock->shouldReceive('__invoke')
            ->with('step 6 result')
            ->andReturnUsing(function() use ($scenario){
                $this->assertEquals('step 1 result', $scenario->getResultFrom('step1'));
                return 'step 3 result final';
            });

        $step4Mock->shouldReceive('__invoke')
            ->with('step 1 result')
            ->andReturn('step 4 result');
        $step5Mock->shouldReceive('__invoke')
            ->with('step 4 result')
            ->andReturn('step 5 result');
        $step6Mock->shouldReceive('__invoke')
            ->with('step 5 result')
            ->andReturn('step 6 result');

        BasicScenario::extend(
            function (BasicScenario $scenario) use ($step4Mock, $step5Mock, $step6Mock) {
                $scenario->insertAfter('step1', $step4Mock);
                $scenario->insertBefore('step3', $step6Mock);
                $scenario->replace('step2', $step5Mock);
            }
        );

        $scenario->setDispatcher($this->dispatcherMock);
        $scenario->inject($eventMock);
        $scenario->add($step1Mock);
        $scenario->add($step2Mock);
        $scenario->add($step3Mock);
        $result = $scenario->run();

        $step2Mock->shouldNotHaveReceived('__invoke');
        $this->assertEquals('step 3 result final', $result);
    }

    public function testActionsCanDispatchEventsToOtherScenarios()
    {
        $eventMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');

        $eventMock->shouldReceive('getEventName')->andReturn('test.event');
        $eventMock->shouldReceive('getExpectedReactions')->andReturn(['some reaction']);
        $eventMock->shouldReceive('getEventData')->andReturn([1, 2, 3]);

        $step1Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $step2Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $step3Mock = Mockery::mock('Keios\Apparatus\Core\Step');

        $step1Mock->shouldReceive('setScenario')->with(Mockery::type('Keios\Apparatus\Contracts\Runnable'));
        $step2Mock->shouldReceive('setScenario')->with(Mockery::type('Keios\Apparatus\Contracts\Runnable'));
        $step3Mock->shouldReceive('setScenario')->with(Mockery::type('Keios\Apparatus\Contracts\Runnable'));

        $step1Mock->shouldReceive('getName')->andReturn('step1');
        $step2Mock->shouldReceive('getName')->andReturn('step2');
        $step3Mock->shouldReceive('getName')->andReturn('step3');

        $step1Mock->shouldReceive('getTriggeringEvents')->andReturn(['test.event']);
        $step2Mock->shouldReceive('getTriggeringEvents')->andReturn([]);
        $step3Mock->shouldReceive('getTriggeringEvents')->andReturn([]);

        $this->dispatcherMock->shouldReceive('dispatch')->with(Mockery::type('Keios\Apparatus\Contracts\Dispatchable'));
        BasicScenario::resetStaticState();
        $scenario = new BasicScenario();

        $step1Mock->shouldReceive('__invoke')
            ->with([1, 2, 3])
            ->andReturn('step 1 result');
        $step2Mock->shouldReceive('__invoke')
            ->with('step 1 result')
            ->andReturnUsing(function() use ($scenario){
                $scenario->dispatch('some.event', [4,5,6], ['another.reaction']);
                return 'step 2 result';
            });
        $step3Mock->shouldReceive('__invoke')
            ->with('step 2 result')
            ->andReturn('step 3 result final');

        $scenario->setDispatcher($this->dispatcherMock);
        $scenario->inject($eventMock);
        $scenario->add($step1Mock);
        $scenario->add($step2Mock);
        $scenario->add($step3Mock);
        $result = $scenario->run();

        $step3Mock->shouldHaveReceived('__invoke');
        $this->assertEquals('step 3 result final', $result);
        $this->dispatcherMock->shouldHaveReceived('dispatch')->with(Mockery::type('Keios\Apparatus\Contracts\Dispatchable'));
    }

    public function tearDown()
    {
        \Mockery::close();
    }
} 