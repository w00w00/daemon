<?php
namespace Daemon\Action;

use Daemon\System\Dispatcher;

abstract class Action
{
    protected $_requiredParams = array();

    /**
     *
     * @var Dispatcher
     */
    protected $_dispatcher;

    static public function factory($actionName, Dispatcher $dispatcher)
    {
        $actionName = ucfirst($actionName);
        $className = "Application\\Action\\$actionName";
        $action = new $className();

        if (!$action instanceof Action) {
            throw new \UnexpectedValueException(
                'action must be subclass of Action'
            );
        }

        $action->setDispatcher($dispatcher);

        return $action;
    }

    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->_dispatcher = $dispatcher;
        return $this;
    }

    public function getParam($name, $default = null)
    {
        return $this->_dispatcher->getArgumentValue($name)
            ? : $default;
    }

    public function __get($name)
    {
        return $this->getParam($name);
    }

    protected function _validateParams()
    {
        foreach ($this->_requiredParams as $required) {
            if (!$this->getParam($required)) {
                throw new \UnexpectedValueException(
                    "$required param is required"
                );
            }
        }
    }

    protected function _beforeExecute()
    {
        //callback
    }

    final public function execute()
    {
        $this->_validateParams();
        $this->_beforeExecute();
        $this->_execute();
    }

    abstract protected function _execute();
}