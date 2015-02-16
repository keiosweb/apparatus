<?php namespace Keios\Apparatus\Tests;

use Keios\Apparatus\Core\ScenarioConfiguration;

class TestScenarioConfiguration extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Keios\Apparatus\Exceptions\ScenarioNotFoundException
     */
    public function testNonExistentClassesCannotBeRegistered()
    {
        $scenarioConfiguration = new ScenarioConfiguration();
        $scenarioConfiguration->bind('test.event', 'NonExistentClass');
    }

    /**
     * @expectedException \Keios\Apparatus\Exceptions\InvalidScenarioException
     */
    public function testNonPlayableClassesCannotBeRegistered()
    {
        $scenarioConfiguration = new ScenarioConfiguration();
        $scenarioConfiguration->bind('test.event', 'Keios\Apparatus\Tests\Fixtures\NotAScenario');
    }

    public function testScenariosCanBeRegistered()
    {
        $scenarioConfiguration = new ScenarioConfiguration();
        $scenarioConfiguration->bind('test.event', 'Keios\Apparatus\Tests\Fixtures\ChainedEventsScenario');

        $this->assertEquals(
            ['test.event' => 'Keios\Apparatus\Tests\Fixtures\ChainedEventsScenario'],
            $scenarioConfiguration->loadScenarios()
        );
    }

    public function tearDown()
    {
        \Mockery::close();
    }

}