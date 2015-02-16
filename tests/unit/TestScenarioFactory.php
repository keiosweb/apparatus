<?php namespace Keios\Apparatus\Tests;

use Keios\Apparatus\Core\ScenarioFactory;
use Keios\Apparatus\Tests\Fixtures\NotAScenario;
use Keios\Apparatus\Tests\Fixtures\TestScenario;
use Mockery;

class TestScenarioFactory extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCanBeInitialized()
    {
        $loaderMock = Mockery::mock('Keios\Apparatus\Contracts\LoaderInterface');
        $loaderMock->shouldReceive('loadScenarios')->andReturn(['test.event' => 'Keios\Apparatus\Tests\Fixtures\TestScenario']);

        new ScenarioFactory($loaderMock);
    }

    /**
     * @expectedException \Keios\Apparatus\Exceptions\InvalidScenarioConfigurationException
     */
    public function testFactoryCantBeInitializedWithWrongLoaderImplementation()
    {
        $loaderMock = Mockery::mock('Keios\Apparatus\Contracts\LoaderInterface');
        $loaderMock->shouldReceive('loadScenarios')->andReturn('not a valid configuration');

        new ScenarioFactory($loaderMock);
    }

    public function testFactoryCanFindHandlerScenarioAndInstantiateScenario()
    {
        $loaderMock = Mockery::mock('Keios\Apparatus\Contracts\LoaderInterface');
        $loaderMock->shouldReceive('loadScenarios')->andReturn(['test.event' => 'Keios\Apparatus\Tests\Fixtures\TestScenario']);
        $dispatchMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');
        $dispatchMock->shouldReceive('getEventName')->andReturn('test.event');

        $factory = new ScenarioFactory($loaderMock);

        $this->assertEquals(new TestScenario(), $factory->findHandlerScenarioFor($dispatchMock));
    }

    /**
     * @expectedException \Keios\Apparatus\Exceptions\NoHandlerScenarioFoundException
     */
    public function testThrowExceptionIfNoHandlerForEventIsFound()
    {
        $loaderMock = Mockery::mock('Keios\Apparatus\Contracts\LoaderInterface');
        $loaderMock->shouldReceive('loadScenarios')->andReturn(['test.event.other' => 'Keios\Apparatus\Tests\Fixtures\TestScenario']);
        $dispatchMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');
        $dispatchMock->shouldReceive('getEventName')->andReturn('test.event');

        $factory = new ScenarioFactory($loaderMock);

        $this->assertEquals(new TestScenario(), $factory->findHandlerScenarioFor($dispatchMock));
    }

    public function testClosuresCanBeResolvedToInstantiateScenarios()
    {
        $loaderMock = Mockery::mock('Keios\Apparatus\Contracts\LoaderInterface');
        $loaderMock->shouldReceive('loadScenarios')->andReturn(
            [
                'test.event' => function () {
                    return 'Keios\Apparatus\Tests\Fixtures\TestScenario';
                }
            ]
        );
        $dispatchMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');
        $dispatchMock->shouldReceive('getEventName')->andReturn('test.event');

        $factory = new ScenarioFactory($loaderMock);

        $this->assertEquals(new TestScenario(), $factory->findHandlerScenarioFor($dispatchMock));
    }

    public function testClosuresCanReturnReadyInstances()
    {
        $loaderMock = Mockery::mock('Keios\Apparatus\Contracts\LoaderInterface');
        $loaderMock->shouldReceive('loadScenarios')->andReturn(
            [
                'test.event' => function () {
                    return new TestScenario();
                }
            ]
        );
        $dispatchMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');
        $dispatchMock->shouldReceive('getEventName')->andReturn('test.event');

        $factory = new ScenarioFactory($loaderMock);

        $this->assertEquals(new TestScenario(), $factory->findHandlerScenarioFor($dispatchMock));
    }

    /**
     * @expectedException \Keios\Apparatus\Exceptions\InvalidScenarioException
     */
    public function testExceptionWillBeThrownIfClosureProvidesInvalidScenarioClass()
    {
        $loaderMock = Mockery::mock('Keios\Apparatus\Contracts\LoaderInterface');
        $loaderMock->shouldReceive('loadScenarios')->andReturn(
            [
                'test.event' => function () {
                    return new NotAScenario();
                }
            ]
        );
        $dispatchMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');
        $dispatchMock->shouldReceive('getEventName')->andReturn('test.event');

        $factory = new ScenarioFactory($loaderMock);

        $factory->findHandlerScenarioFor($dispatchMock);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

}