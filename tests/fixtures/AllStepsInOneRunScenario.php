<?php namespace Keios\Apparatus\Tests\Fixtures;

use Keios\Apparatus\Contracts\Runnable;
use Keios\Apparatus\Core\Scenario;
use Keios\Apparatus\Core\Step;

class AllStepsInOneRunScenario extends Scenario implements Runnable
{
    protected function setUp()
    {
        $this->add(
            new Step(
                'parsing event data',
                function (Scenario $scenario, $eventData) {
                    $eventExpects = $this->getExpectedReactions(); // or $scenario->getExpectedReaction();

                    if (!in_array('a benevolent alien!', $eventExpects)) {
                        throw new \Exception('Oh my, we cannot respond to unknown expectation!');
                    }

                    $eventData = $scenario->getEventData(); // or $this->getEventData(), we can do this in Closures

                    if ($eventData->threat !== 'Daleks') {
                        throw new \Exception('Hmmm, no threat at all!');
                    }

                    // let's do something here
                    return ['an', 'array', 'of', 'daleks'];
                },
                ['event.triggering.all.steps.in.one.run.scenario']
            )
        );

        $this->add(
            new Step(
                'performing some actions on results of first step',
                function (Scenario $scenario, $resultOfFirstStep) {
                    if ($resultOfFirstStep !== ['an', 'array', 'of', 'daleks']) {
                        throw new \Exception('Oh damn!, something is not right! We were expecting the Daleks!');
                    }

                    // perform some database operations here maybe?
                    $result = new \stdClass();
                    $result->id = 23;
                    $result->name = 'The Doctor';
                    $result->question = 'Who';

                    return $result;
                },
                [] // not handling any event, just doing stuff
            )
        );

        $this->add(
            new Step(
                'caching some stuff maybe?',
                function (Scenario $scenario, $tardisUserObject) {
                    if ($tardisUserObject->name !== 'The Doctor') {
                        throw new \Exception('Damn Daleks! Where is the Doctor?');
                    }

                    // cache the result some way here and return it to event sender!
                    return $tardisUserObject;
                },
                [] // no events for this step
            )
        );
    }
}