<?php namespace Keios\Apparatus\Tests;

use Keios\Apparatus\Core\Step;
use Keios\Apparatus\Tests\Fixtures\DispatchingTestEventAction;
use Keios\Apparatus\Tests\Fixtures\SomeSideAction;
use Mockery;

class TestStep extends \PHPUnit_Framework_TestCase
{
    public function testStepCanBeInstantiatedWithClosure()
    {
        $step = new Step(
            'testing closure instantiation of Step class', function () {
            return 'some result of the step';
        }, ['some.event']
        );

        $resultOfTheStep = $step(false);

        $this->assertEquals('testing closure instantiation of Step class', $step->getName());
        $this->assertEquals('some result of the step', $resultOfTheStep);
        $this->assertEquals(['some.event'], $step->getTriggeringEvents());
    }

    public function testThatThisInClosuresRefersToScenarioInstance()
    {
        $scenarioMock = Mockery::mock('Keios\Apparatus\Contracts\Runnable');

        $scenarioMock->shouldReceive('dispatch')
            ->withArgs(['test.event', [1, 2, 3], ['some', 'reactions']])
            ->andReturn('a reaction');

        $step = new Step(
            'dispatching another event',
            function () {
                return $this->dispatch('test.event', [1, 2, 3], ['some', 'reactions']);
            }
        );

        $step->setScenario($scenarioMock);

        $resultOfTheStep = $step(false);

        $this->assertEquals('dispatching another event', $step->getName());
        $this->assertEquals('a reaction', $resultOfTheStep);
    }

    public function testThatProtectedScenarioValuesAreNotAccessibleFromClosures()
    {
        $scenarioMock = Mockery::mock('Keios\Apparatus\Contracts\Runnable');

        $scenarioMock->shouldReceive('dispatch')
            ->withArgs(['test.event', [1, 2, 3], ['some', 'reactions']])
            ->andReturn('a reaction');

        $step = new Step(
            'dispatching another event',
            function () {
                return $this->dispatch('test.event', [1, 2, 3], ['some', 'reactions']);
            },
            ['some.event']
        );

        $step->setScenario($scenarioMock);

        $resultOfTheStep = $step(false);

        $this->assertEquals('dispatching another event', $step->getName());
        $this->assertEquals('a reaction', $resultOfTheStep);
    }

    public function testCallableClassesCanBeUsedAsClosuresInSteps()
    {
        $scenarioMock = Mockery::mock('Keios\Apparatus\Contracts\Runnable');

        $scenarioMock->shouldReceive('dispatch')
            ->withArgs(['test.event', [1, 2, 3], ['some', 'reactions']])
            ->andReturn('a reaction');

        $step = new Step('dispatching another event', new DispatchingTestEventAction);

        $step->setScenario($scenarioMock);

        $resultOfTheStep = $step(false);

        $this->assertEquals('dispatching another event', $step->getName());
        $this->assertEquals('a reaction', $resultOfTheStep);
    }

    public function testSideActionsAreCalledWithMainActionResult()
    {
        $scenarioMock = Mockery::mock('Keios\Apparatus\Contracts\Runnable');
        $sideActionMock = Mockery::mock('Keios\Apparatus\Core\SideAction');
        $anotherSideActionMock = Mockery::mock('Keios\Apparatus\Core\SideAction');

        $scenarioMock->shouldReceive('dispatch')
            ->withArgs(['test.event', [1, 2, 3], ['some', 'reactions']])
            ->andReturn('a reaction');

        $sideActionMock->shouldReceive('__invoke')->with($scenarioMock, 'a reaction');
        $anotherSideActionMock->shouldReceive('__invoke')->with($scenarioMock, 'a reaction');

        Step::addSideAction('dispatching another event', $sideActionMock);
        Step::addSideAction('dispatching another event', $anotherSideActionMock);
        Step::addSideAction('dispatching another event', new SomeSideAction); //test SideAction class

        $step = new Step('dispatching another event', new DispatchingTestEventAction);

        $step->setScenario($scenarioMock);

        $resultOfTheStep = $step(false);

        $this->assertEquals('dispatching another event', $step->getName());
        $this->assertEquals('a reaction', $resultOfTheStep);
    }
}
