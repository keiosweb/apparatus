<?php namespace Keios\Apparatus\Tests;

use Keios\Apparatus\Core\ScenarioRepository;
use Mockery;

class TestScenarioRepository extends \PHPUnit_Framework_TestCase
{
    public function testRepositoryCanBeInitialized()
    {
        $factoryMock = Mockery::mock('Keios\Apparatus\Core\ScenarioFactory');
        $scenarioMock = Mockery::mock('Keios\Apparatus\Contracts\Playable');

        $factoryMock->shouldReceive('findHandlerScenarioFor')->with(Mockery::type('Keios\Apparatus\Contracts\Dispatchable'))->andReturn($scenarioMock);

        new ScenarioRepository($factoryMock);
    }

    public function testRepositoryCanFindHandlerScenarioAndReturnInstance()
    {
        $dispatchMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');
        $dispatchMock->shouldReceive('getEventName')->andReturn('test.event');
        $factoryMock = Mockery::mock('Keios\Apparatus\Core\ScenarioFactory');
        $scenarioMock = Mockery::mock('Keios\Apparatus\Contracts\Playable');

        $factoryMock->shouldReceive('findHandlerScenarioFor')->with(Mockery::type('Keios\Apparatus\Contracts\Dispatchable'))->andReturn($scenarioMock);

        $repository = new ScenarioRepository($factoryMock);

        $this->assertEquals($scenarioMock, $repository->findHandlerScenarioFor($dispatchMock));
    }

    public function tearDown()
    {
        \Mockery::close();
    }
}