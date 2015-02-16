<?php namespace Keios\Apparatus\Tests;

use Keios\Apparatus\Core\Event;
use Keios\Apparatus\Core\Dispatcher;
use Keios\Apparatus\Core\ScenarioConfiguration;
use Keios\Apparatus\Core\ScenarioFactory;
use Keios\Apparatus\Core\ScenarioRepository;
use Keios\Apparatus\Core\ScenarioRunner;
use Mockery;


class FunctionalTestSimpleDispatchAndReaction extends \PHPUnit_Framework_TestCase
{

    public function testUseTheWholeApparatus()
    {
        $scenarioConfiguration = new ScenarioConfiguration();
        $scenarioConfiguration->bind('test.event', 'Keios\Apparatus\Tests\Fixtures\TestScenario');

        $dispatcher = new Dispatcher(new ScenarioRepository(new ScenarioFactory($scenarioConfiguration)),
            new ScenarioRunner());

        $event = new Event($dispatcher);

        $event->name('test.event')->with(['clicked' => 'A'])->expect(['test.string']);

        $reaction = $event->getReaction();

        $this->assertEquals('A was clicked', $reaction);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThatScenarioWillThrowExceptionIfExpectedReactionIsInvalid()
    {
        $scenarioConfiguration = new ScenarioConfiguration();
        $scenarioConfiguration->bind('test.event', 'Keios\Apparatus\Tests\Fixtures\TestScenario');

        $dispatcher = new Dispatcher(new ScenarioRepository(new ScenarioFactory($scenarioConfiguration)),
            new ScenarioRunner());

        $event = new Event($dispatcher);

        $event->name('test.event')->with(['clicked' => 'A'])->expect(['some.string'])->getReaction();
    }

    public function tearDown()
    {
        \Mockery::close();
    }
} 