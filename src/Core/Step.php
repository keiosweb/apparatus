<?php namespace Keios\Apparatus\Core;

use Keios\Apparatus\Contracts\Runnable;

/**
 * Class Step
 *
 * @package Keios\Apparatus
 */
class Step
{
    /**
     * @var array
     */
    protected static $sideActionsCache = [];

    /**
     * @var
     */
    protected $name;

    /**
     * @var callable
     */
    protected $action;

    /**
     * @var
     */
    protected $scenario;

    /**
     * @var array
     */
    protected $triggeringEvents;

    /**
     * @var array
     */
    protected $sideActions = [];

    /**
     * @param          $name
     * @param callable $action
     * @param array    $triggeringEvents
     */
    public function __construct($name, callable $action, array $triggeringEvents = null)
    {
        $this->name = $name;
        $this->action = $action;
        $this->triggeringEvents = $triggeringEvents ?: [];

        $this->importSideActions();
    }

    /**
     * @param \Keios\Apparatus\Contracts\Runnable $scenario
     */
    public function setScenario(Runnable $scenario)
    {
        $this->scenario = $scenario;
    }

    /**
     * @param $lastStepResult
     *
     * @return mixed
     */
    public function __invoke($lastStepResult)
    {
        $action = $this->action;

        $action = $this->bindToScenario($action);

        $result = $action($this->scenario, $lastStepResult);

        $this->executeSideActions($result);

        return $result;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getTriggeringEvents()
    {
        return $this->triggeringEvents;
    }

    /**
     *
     */
    protected function importSideActions()
    {
        if (isset(static::$sideActionsCache[$this->name]) && is_array(static::$sideActionsCache[$this->name])) {
            $this->sideActions = static::$sideActionsCache[$this->name];
        }
    }

    /**
     * @param callable $action
     *
     * @return callable
     */
    protected function bindToScenario(callable $action)
    {
        if ($action instanceof \Closure) {
            return $action->bindTo($this->scenario);
        } else {
            return $action;
        }
    }

    /**
     * @param $result
     */
    protected function executeSideActions($result)
    {
        foreach ($this->sideActions as $sideAction) {
            $sideAction = $this->bindToScenario($sideAction);
            $sideAction($this->scenario, $result);
        }
    }

    /**
     * @param          $stepName
     * @param callable $sideAction
     */
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