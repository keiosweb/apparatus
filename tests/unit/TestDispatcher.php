<?php namespace Keios\Apparatus\Tests;

use Mockery;
use Keios\Apparatus\Core\Dispatcher;

class TestDispatcher extends \PHPUnit_Framework_TestCase
{
    public function testDispatchEventToScenarioRunner()
    {
        $scenarioRepositoryMock = Mockery::mock('Keios\Apparatus\Core\ScenarioRepository');
        $scenarioRunnerMock = Mockery::mock('Keios\Apparatus\Core\ScenarioRunner');
        $dispatchMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');
        $scenarioMock = Mockery::mock('Keios\Apparatus\Contracts\Playable');

        $scenarioRepositoryMock->shouldReceive('findHandlerScenarioFor')->with(Mockery::type('Keios\Apparatus\Contracts\Dispatchable'))->andReturn($scenarioMock);
        $scenarioRunnerMock->shouldReceive('setDispatcher')->with(Mockery::type('Keios\Apparatus\Core\Dispatcher'));
        $scenarioRunnerMock->shouldReceive('run')->withArgs([
            Mockery::type('Keios\Apparatus\Contracts\Playable'),
            Mockery::type('Keios\Apparatus\Contracts\Dispatchable')
        ])->andReturn('reaction');

        $dispatcher = new Dispatcher($scenarioRepositoryMock, $scenarioRunnerMock);

        $dispatcher->dispatch($dispatchMock);
    }

    public function tearDown()
    {
        \Mockery::close();
    }
} 