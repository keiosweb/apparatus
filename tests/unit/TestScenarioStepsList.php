<?php namespace Keios\Apparatus\Tests;

use Keios\Apparatus\Core\ScenarioStepsList;
use Mockery;

class TestScenarioStepsList extends \PHPUnit_Framework_TestCase
{
    protected $step1Mock;
    protected $step2Mock;
    protected $step3Mock;
    protected $step4Mock;
    protected $stepNamedLikeStep1Mock;
    protected $invalidStep;
    protected $scenarioMock;

    public function setUp()
    {
        $this->step1Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $this->step2Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $this->step3Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $this->step4Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $this->stepNamedLikeStep1Mock = Mockery::mock('Keios\Apparatus\Core\Step');
        $this->invalidStep = Mockery::mock('Keios\Apparatus\Core\Step');
        $this->scenarioMock = Mockery::mock('Keios\Apparatus\Contracts\Runnable');

        $this->step1Mock->shouldReceive('getName')->andReturn('step1');
        $this->step2Mock->shouldReceive('getName')->andReturn('step2');
        $this->step3Mock->shouldReceive('getName')->andReturn('step3');
        $this->step4Mock->shouldReceive('getName')->andReturn('step4');
        $this->invalidStep->shouldReceive('getName')->andReturn('invalid');
        $this->stepNamedLikeStep1Mock->shouldReceive('getName')->andReturn('step1');

        $this->step1Mock->shouldReceive('getTriggeringEvents')->andReturn(['test.event.1']);
        $this->step2Mock->shouldReceive('getTriggeringEvents')->andReturn(['test.event.2']);
        $this->step3Mock->shouldReceive('getTriggeringEvents')->andReturn(['test.event.3']);
        $this->step4Mock->shouldReceive('getTriggeringEvents')->andReturn(['test.event.4']);
        $this->invalidStep->shouldReceive('getTriggeringEvents')
            ->andReturn(['test.event.5', 'test.event.1']); // has same triggering event as step1
        $this->stepNamedLikeStep1Mock->shouldReceive('getTriggeringEvents')->andReturn(['completely.different.event']);
    }

    public function testThatAddingStepsRegistersEvents()
    {
        $list = new ScenarioStepsList($this->scenarioMock);
        $list->add($this->step1Mock);
        $list->add($this->step2Mock);
        $list->add($this->step3Mock);

        $this->assertEquals($this->step3Mock, $list->getStepForEvent('test.event.3'));
        $this->assertEquals($this->step1Mock, $list->getStepForEvent('test.event.1'));
    }

    /**
     * @expectedException \Keios\Apparatus\Exceptions\NoStepsDefinedException
     */
    public function testCanNotBeInitializedWithoutSteps()
    {
        $list = new ScenarioStepsList($this->scenarioMock);
        $list->initialize('not.in.there.event');
    }

    public function testCanAddStepsAndPositionIndexWithEventName()
    {
        $list = new ScenarioStepsList($this->scenarioMock);
        $list->add($this->step1Mock);
        $list->add($this->step2Mock);
        $list->add($this->step3Mock);
        $list->initialize('test.event.2');

        $this->assertEquals($this->step2Mock, $list->getCurrentStep());
    }

    public function testNextStepReturnsCorrectStep()
    {
        $list = new ScenarioStepsList($this->scenarioMock);
        $list->add($this->step1Mock);
        $list->add($this->step2Mock);
        $list->add($this->step3Mock);
        $list->initialize('test.event.2');

        $this->assertEquals($this->step3Mock, $list->getNextStep());
    }

    public function testPreviousStepReturnsCorrectStep()
    {
        $list = new ScenarioStepsList($this->scenarioMock);
        $list->add($this->step1Mock);
        $list->add($this->step2Mock);
        $list->add($this->step3Mock);
        $list->initialize('test.event.2');

        $this->assertEquals($this->step1Mock, $list->getPreviousStep());
    }

    public function testListCanTellIfItContainsStep()
    {
        $list = new ScenarioStepsList($this->scenarioMock);
        $list->add($this->step1Mock);
        $list->add($this->step2Mock);
        $list->add($this->step3Mock);

        $this->assertTrue($list->hasStepNamed('step2'));
    }

    public function testInsertionsArePlacedCorrectlyInTheList()
    {
        $list = new ScenarioStepsList($this->scenarioMock);
        $list->add($this->step1Mock);
        $list->add($this->step2Mock);

        $list->insertAfter('step1', $this->step4Mock);
        $list->insertBefore('step1', $this->step3Mock);

        $list->initialize('test.event.1');

        $this->assertEquals($this->step4Mock, $list->getNextStep());
        $list->setCurrentStep('step3');
        $this->assertEquals($this->step1Mock, $list->getNextStep());
    }

    /**
     * @expectedException \Keios\Apparatus\Exceptions\NoStepForEventFoundException
     */
    public function testStepsCanBeReplacedCorrectly()
    {
        $list = new ScenarioStepsList($this->scenarioMock);
        $list->add($this->step1Mock);
        $list->add($this->step2Mock);
        $list->add($this->step3Mock);

        $list->replace('step2', $this->step4Mock);

        $list->initialize('test.event.1');

        $this->assertEquals($this->step4Mock, $list->getNextStep());

        $list->getStepForEvent('test.event.2');
    }

    /**
     * @expectedException \Keios\Apparatus\Exceptions\NoStepWithNameFoundException
     */
    public function testStepsHaveToExistToBeReplaced()
    {
        $list = new ScenarioStepsList($this->scenarioMock);
        $list->add($this->step1Mock);
        $list->add($this->step2Mock);
        $list->add($this->step3Mock);

        $list->replace('step4', $this->step4Mock);
    }

    /**
     * @expectedException \Keios\Apparatus\Exceptions\NotInitializedException
     */
    public function testCannotGetNextStepWhenNotInitialized()
    {
        $list = new ScenarioStepsList($this->scenarioMock);
        $list->getNextStep();
    }

    /**
     * @expectedException \Keios\Apparatus\Exceptions\StepAlreadyRegisteredException
     */
    public function testStepsHaveToBeUniquelyNamed()
    {
        $list = new ScenarioStepsList($this->scenarioMock);
        $list->add($this->step1Mock);
        $list->add($this->stepNamedLikeStep1Mock);
    }

    /**
     * @expectedException \Keios\Apparatus\Exceptions\EventAlreadyRegisteredException
     */
    public function testCantRegisterSameEventMoreThanOnce()
    {
        $list = new ScenarioStepsList($this->scenarioMock);
        $list->add($this->step1Mock);
        $list->add($this->step2Mock);
        $list->add($this->step3Mock);
        $list->add($this->invalidStep);
    }

    public function testNavigationMethods()
    {
        $list = new ScenarioStepsList($this->scenarioMock);
        $list->add($this->step1Mock);
        $list->add($this->step2Mock);
        $list->initialize('test.event.1');

        // no steps before first
        $this->assertFalse($list->hasPreviousStep());
        // but one more step to go after first
        $this->assertTrue($list->hasNextStep());
        // move to second step
        $list->moveToNextStep();
        // check current step is second step
        $this->assertEquals($this->step2Mock, $list->getCurrentStep());
        // we have no more steps after second
        $this->assertFalse($list->hasNextStep());
        // try to move to next, list should stay at last step if there are none left
        $list->moveToNextStep();
        $this->assertEquals($this->step2Mock, $list->getCurrentStep());
        // move to first step again
        $list->moveToPreviousStep();
        $this->assertEquals($this->step1Mock, $list->getCurrentStep());
        // still no steps before first
        $this->assertFalse($list->hasPreviousStep());
        // insert one step before first
        $list->insertBefore('step1', $this->step3Mock);
        // now there should be one
        $this->assertTrue($list->hasPreviousStep());
        $list->moveToPreviousStep();
        // but no previous before inserted steps
        $this->assertFalse($list->hasPreviousStep());
        // so we should be at step3
        $this->assertEquals($this->step3Mock, $list->getCurrentStep());
        // try to move to previous, list should not change its pointer position
        $list->moveToPreviousStep();
        $this->assertEquals($this->step3Mock, $list->getCurrentStep());
    }
}