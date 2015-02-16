<?php namespace Keios\Apparatus\Tests\Fixtures;

use Keios\Apparatus\Contracts\Runnable;
use Keios\Apparatus\Core\Scenario;

class TestScenario extends Scenario implements Runnable
{
    protected function setUp()
    {
    }

    /**
     * TESTS ONLY
     */
    public static function resetStaticState()
    {
        static::$registeredCallbacks = [];
    }
} 