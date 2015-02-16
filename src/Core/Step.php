<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Runnable;

class Step
{
    protected static $sideActionsCache = [];

    protected $name;

    protected $action;

    protected $scenario;

    protected $triggeringEvents;

    protected $sideActions = [];

    public function __construct($name, callable $action, array $triggeringEvents = null)
    {
        $this->name = $name;
        $this->action = $action;
        $this->triggeringEvents = $triggeringEvents ?: [];

        $this->importSideActions();
    }

    public function setScenario(Runnable $scenario)
    {
        $this->scenario = $scenario;
    }

    public function __invoke($lastStepResult)
    {
        $action = $this->action;

        $action = $this->bindToScenario($action);

        $result = $action($this->scenario, $lastStepResult);

        $this->executeSideActions($result);

        return $result;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTriggeringEvents()
    {
        return $this->triggeringEvents;
    }

    protected function importSideActions()
    {
        if (isset(static::$sideActionsCache[$this->name]) && is_array(static::$sideActionsCache[$this->name])) {
            $this->sideActions = static::$sideActionsCache[$this->name];
        }
    }

    protected function bindToScenario(callable $action)
    {
        if ($action instanceof \Closure) {
            return $action->bindTo($this->scenario);
        } else {
            return $action;
        }
    }

    protected function executeSideActions($result)
    {
        foreach ($this->sideActions as $sideAction) {
            $sideAction = $this->bindToScenario($sideAction);
            $sideAction($this->scenario, $result);
        }
    }

    public static function addSideAction($stepName, callable $sideAction)
    {
        if (isset(static::$sideActionsCache[$stepName]) && is_array(static::$sideActionsCache[$stepName])) {
            static::$sideActionsCache[$stepName][] = $sideAction;
        } else {
            static::$sideActionsCache[$stepName] = [];
            static::$sideActionsCache[$stepName][] = $sideAction;
        }
    }


}