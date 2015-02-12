<?php namespace Keios\Apparatus\Tests\Fixtures;

use Keios\Apparatus\Contracts\Playable;
use Keios\Apparatus\Core\Scenario;

class TestScenario extends Scenario implements Playable
{

    public function play($data, array $expectedReactions)
    {
        if (!in_array('test.string', $expectedReactions)) {
            throw new \InvalidArgumentException('We can only return test.string!');
        }

        if ($data['clicked'] === 'A') {
            return 'A was clicked';
        }
        if ($data['clicked'] === 'B') {
            return 'B was clicked';
        }

        throw new \InvalidArgumentException('Invalid data provided!');
    }

    public function testDispatchingNewEvents()
    {
        $this->dispatch('test.event', [1, 2, 3], ['test.reaction']);
    }
} 