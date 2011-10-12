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

    public function __construct(Dispatcher $dispatcher, array $options = null)
    {
        $this->_dispatcher = $dispatcher;

        $signals = array(SIGALRM, SIGCHLD, SIGTERM);

        foreach ($signals as $signal) {
            pcntl_signal($signal, array($this, '_signalHandler'));
        }
    }

    protected function _signalHandler($signal)
    {
        switch ($signal) {
            case SIGALRM:
            case SIGCHLD:
                pcntl_wait($status, WUNTRACED);
                break;
            case SIGTERM:
                die;
                break;
        }
    }

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