<?php
namespace Daemon\System\Daemon;

use Daemon\System\Dispatcher,
    Daemon\Standard\IDaemon;

/**
 * Abstract class that implements the action interface, its used to spawn
 * different daemons its like a controller on a mvc
 *
 * @author Jhonatan Teixeira
 */
abstract class Runner implements IDaemon
{
    /**
     *
     * @var Dispatcher
     */
    protected $_dispatcher;
    protected $_isChild = false;

    /**
     *
     * @param <type> $class
     * @param Dispatcher $dispatcher
     * @return IDaemon 
     */
    public static function factory($class, Dispatcher $dispatcher)
    {
        $daemonClass = "Application\\Daemon\\" . ucfirst($class);
        $daemon = new $daemonClass();

        if (!$daemon instanceof IDaemon) {
            throw new \Exception('Daemon class must implement IDaemon');
        }

        $daemon->setDispatcher($dispatcher);

        return $daemon;
    }

    public static function isDying()
    {
        return Starter::isDying();
    }

    /**
     *
     * @param Dispatcher $dispatcher
     * @return Runner 
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->_dispatcher = $dispatcher;

        return $this;
    }

    /**
     *
     * @param <type> $name
     * @param <type> $withValue
     * @return <type> 
     */
    protected function _getArgument($name, $withValue = false)
    {
        return ($withValue)
            ? $this->_dispatcher->getArgumentValue($name)
            : $this->_dispatcher->getArgument($name);
    }

    /**
     *
     * @param <type> $name
     * @return <type> 
     */
    protected function _hasArgument($name)
    {
        return $this->_dispatcher->hasArgument($name);
    }

    /**
     *
     * @param <type> $info 
     */
    protected function _logInfo($info)
    {
        $this->_log($info);
    }

    /**
     *
     * @param <type> $content
     * @param <type> $level 
     */
    protected function _log($content, $level = 1)
    {
        
    }

    /**
     * 
     */
    protected function _fork($task = null)
    {
        $pid = pcntl_fork();

        switch ($pid) {
            case -1:
                $this->_log("failed to fork a child\n");
                break;
            case 0:
                $this->_isChild = true;
                $this->_childCallBack();
                break;
            default:
                pcntl_wait($status, WNOHANG | WUNTRACED);
                $this->_parentCallBack($pid);
                break;
        }
    }

    /**
     *
     * @param <type> $seconds 
     */
    protected function _iterate($seconds)
    {
        sleep($seconds);
        
        clearstatcache();
        
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }
}