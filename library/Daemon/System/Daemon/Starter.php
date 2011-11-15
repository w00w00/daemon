<?php
namespace Daemon\System\Daemon;

use Daemon\System\Dispatcher;

/**
 * daemonize a process using a daemon class
 *
 * @author Jhonatan Teixeira
 */
class Starter
{
    protected $_dispatcher;
    private static $_isDying = false;

    /**
     * recieve the dispatcher and uses the runner factory, after factoring the
     * requested daemon, it exits and leave the spawned process as a daemon.
     * then it waits for a sigterm and sets the state as dying, hopefully the
     * daemon classes uses this state to stop their loop
     *
     * @param Dispatcher $dispatcher
     * @param array $options 
     */
    public function __construct(Dispatcher $dispatcher, array $options = null)
    {
        $this->_dispatcher = $dispatcher;

        $signals = array(SIGALRM, SIGCHLD, SIGTERM);

        foreach ($signals as $signal) {
            pcntl_signal($signal, array($this, '_signalHandler'));
        }
    }

    /**
     * installs the sigchild signal so it waits for its childs to stop, also
     * sets the state to dying in case of sigterm
     *
     * @param int $signal
     */
    protected function _signalHandler($signal)
    {
        switch ($signal) {
            case SIGALRM:
            case SIGCHLD:
                pcntl_wait($status, WUNTRACED);
                break;
            case SIGTERM:
                self::$_isDying = true;
                die;
                break;
        }
    }

    /**
     * inform the dying state
     *
     * @return bool
     */
    public static function isDying()
    {
        return self::$_isDying;
    }

    /**
     * Starts and fork the desired daemon based on the dispatcher
     */
    public function start()
    {
        $pid = pcntl_fork();

        switch ($pid) {
            case -1:
                //$this->_log("failed to fork a child\n");
                break;
            case 0:
                Runner::factory($this->_daemonName, $this->_dispatcher);
                break;
            default:
                pcntl_wait($status, WNOHANG | WUNTRACED);
                //parent dies, child becomes a daemon
                exit;
                break;
        }
    }
}