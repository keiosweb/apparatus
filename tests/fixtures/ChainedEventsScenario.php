<?php namespace Keios\Apparatus\Tests\Fixtures;

use Keios\Apparatus\Contracts\Runnable;
use Keios\Apparatus\Core\Scenario;
use Keios\Apparatus\Core\Step;

class ChainedEventsScenario extends Scenario implements Runnable
{
    protected function setUp()
    {
        $this->add(
            new Step(
                'responding to test.event.1',
                function (Scenario $scenario, $eventData) {
                    if ($eventData['clicked'] !== 'A') {
                        throw new \Exception('Oooops, should have received clicked => A!');
                    }

                    $this->waitForInteraction();

                    return 'A was clicked';
                },
                ['test.event.1']
            )
        );

        $this->add(
            new Step(
                'responding to test.event.2',
                function (Scenario $scenario, $eventData) {
                    if ($eventData['clicked'] !== 'B') {
                        throw new \Exception('Oooops, should have received clicked => B!');
                    }

                    $scenario->waitForInteraction();

                    return 'B was clicked';
                },
                ['test.event.2']
            )
        );

        $this->add(
            new Step(
                'responding to test.event.3',
                function (Scenario $scenario, $eventData) {
                    if ($eventData['clicked'] !== 'C') {
                        throw new \Exception('Oooops, should have received clicked => C!');
                    }

                    $this->waitForInteraction();

                    return 'C was clicked';
                },
                ['test.event.3']
            )
        );
    }

    /**
     * TESTS ONLY
     */
    public static function resetStaticState()
    {
        static::$registeredCallbacks = [];
    }
} 