<?php namespace Keios\Apparatus\Tests;

use Keios\Apparatus\Core\Event;
use Keios\Apparatus\Core\Dispatcher;
use Keios\Apparatus\Core\ScenarioConfiguration;
use Keios\Apparatus\Core\ScenarioFactory;
use Keios\Apparatus\Core\ScenarioRepository;
use Keios\Apparatus\Core\ScenarioRunner;

class FunctionalTestSuite extends \PHPUnit_Framework_TestCase
{
    public function testUseTheWholeApparatus()
    {
        /*
         * For reference: check out testing scenario - tests/fixtures/ChainedEventsScenario.php
         *
         * Create a new ScenarioConfiguration object and bind events to our event chaining scenario
         */
        $scenarioConfiguration = new ScenarioConfiguration();
        $scenarioConfiguration->bind('test.event.1', 'Keios\Apparatus\Tests\Fixtures\ChainedEventsScenario');
        $scenarioConfiguration->bind('test.event.2', 'Keios\Apparatus\Tests\Fixtures\ChainedEventsScenario');
        $scenarioConfiguration->bind('test.event.3', 'Keios\Apparatus\Tests\Fixtures\ChainedEventsScenario');

        /*
         * Compose the whole Apparatus Class Hierarchy
         */
        $dispatcher = new Dispatcher(
            new ScenarioRepository(
                new ScenarioFactory($scenarioConfiguration)
            ),
            new ScenarioRunner()
        );

        /*
         * Create 3 events to which our scenario is going to respond
         */
        $event1 = new Event($dispatcher);
        $event2 = new Event($dispatcher);
        $event3 = new Event($dispatcher);

        /*
         * Configure events
         */
        $event1->name('test.event.1')->with(['clicked' => 'A'])->expect(['string']);
        $event2->name('test.event.2')->with(['clicked' => 'B'])->expect(['string']);
        $event3->name('test.event.3')->with(['clicked' => 'C'])->expect(['string']);

        /*
         * And dispatch them, they should give us our expected reactions
         */
        $reaction1 = $event1->getReaction();
        $reaction2 = $event2->getReaction();
        $reaction3 = $event3->getReaction();

        /*
         * Assert we got what we expected
         */
        $this->assertEquals('A was clicked', $reaction1);
        $this->assertEquals('B was clicked', $reaction2);
        $this->assertEquals('C was clicked', $reaction3);
    }

    public function testThatScenarioWillThrowExceptionIfExpectedReactionIsInvalid()
    {
        $scenarioConfiguration = new ScenarioConfiguration();
        $scenarioConfiguration->bind(
            'event.triggering.all.steps.in.one.run.scenario',
            'Keios\Apparatus\Tests\Fixtures\AllStepsInOneRunScenario'
        );

        $dispatcher = new Dispatcher(
            new ScenarioRepository(
                new ScenarioFactory($scenarioConfiguration)
            ),
            new ScenarioRunner()
        );

        $event = new Event($dispatcher);

        $eventData = new \stdClass();
        $eventData->threat = 'Daleks';

        $reactionToThreat = $event->name('event.triggering.all.steps.in.one.run.scenario')
            ->with($eventData)
            ->expect(['a benevolent alien!'])
            ->getReaction();

        $this->assertEquals('The Doctor', $reactionToThreat->name);
    }
} 