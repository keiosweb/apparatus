<?php namespace Keios\Apparatus\Tests;

use Keios\Apparatus\Core\ScenarioRunner;
use Mockery;

class TestScenarioRunner extends \PHPUnit_Framework_TestCase
{

    public function testRunnerCanRunScenarioAndReturnReaction()
    {
        $dispatcherMock = Mockery::mock('Keios\Apparatus\Core\Dispatcher');
        $scenarioMock = Mockery::mock('Keios\Apparatus\Contracts\Playable');
        $dispatchMock = Mockery::mock('Keios\Apparatus\Contracts\Dispatchable');

        $dispatchMock->shouldReceive('getEventData')->andReturn([1, 2, 3]);
        $dispatchMock->shouldReceive('getExpectedReactions')->andReturn(['test.reaction']);

        $scenarioMock->shouldReceive('setDispatcher')->with(Mockery::type('Keios\Apparatus\Core\Dispatcher'));
        $scenarioMock->shouldReceive('play')->withArgs([[1, 2, 3], ['test.reaction']])->andReturn('test reaction');

        $runner = new ScenarioRunner();
        $runner->setDispatcher($dispatcherMock);
        $reaction = $runner->run($scenarioMock, $dispatchMock);

        $this->assertEquals('test reaction', $reaction);

    }

    public function tearDown()
    {
        \Mockery::close();
    }

}