<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Dispatchable;

class Dispatcher
{
    protected $scenarioRepository;

    protected $scenarioRunner;

    public function __construct(ScenarioRepository $scenarioRepository, ScenarioRunner $scenarioRunner)
    {
        $this->scenarioRepository = $scenarioRepository;
        $this->scenarioRunner = $scenarioRunner;
        $this->scenarioRunner->setDispatcher($this);
    }

    public function dispatch(Dispatchable $event)
    {
        $scenario = $this->scenarioRepository->findHandlerScenarioFor($event);

        $reaction = $this->scenarioRunner->run($scenario, $event);

        return $reaction;
    }
} 