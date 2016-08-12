<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Dispatchable;

/**
 * Class Dispatcher
 *
 * @package Keios\Apparatus
 */
class Dispatcher
{
    /**
     * @var \Keios\Apparatus\Core\ScenarioRepository
     */
    protected $scenarioRepository;

    /**
     * @var \Keios\Apparatus\Core\ScenarioRunner
     */
    protected $scenarioRunner;

    /**
     * @param \Keios\Apparatus\Core\ScenarioRepository $scenarioRepository
     * @param \Keios\Apparatus\Core\ScenarioRunner     $scenarioRunner
     */
    public function __construct(ScenarioRepository $scenarioRepository, ScenarioRunner $scenarioRunner)
    {
        $this->scenarioRepository = $scenarioRepository;
        $this->scenarioRunner = $scenarioRunner;
        $this->scenarioRunner->setDispatcher($this);
    }

    /**
     * @param \Keios\Apparatus\Contracts\Dispatchable $event
     *
     * @return mixed
     * @throws \Keios\Apparatus\Exceptions\NoHandlerScenarioFoundException
     */
    public function dispatch(Dispatchable $event)
    {
        $scenario = $this->scenarioRepository->findHandlerScenarioFor($event);

        return $this->scenarioRunner->run($scenario, $event);
    }
} 