<?php
namespace Application\Daemon;

use Daemon\System\Daemon\Runner;

/**
 * @todo since no longer using PEAR System daemon gota test everything up again
 */
class Spool extends Runner
{
    private $_database;
    private $_childArray = array();
    private $_childNum   = 0;
    private $_isChild    = false;

    /**
     * init the signals
     */
    protected function _init()
    {
        $signals = array(SIGALRM, SIGCHLD);
        
        foreach ($signals as $signal) {
            pcntl_signal($signal, array($this, '_signalHandler'));
        }
    }

    /**
     * signal handler routine
     *
     * @param int $signal
     */
    protected function _signalHandler($signal)
    {
        switch ($signal) {
            case SIGALRM:
            case SIGCHLD:
                foreach ($this->_childArray as $key=>$pid) {
                    pcntl_waitpid($pid, $status, WNOHANG | WUNTRACED);
                    if ($pid > 0) {
                        unset($this->_childArray[$key]);
                        $this->_updateChildNum();
                    }
                }
                break;
        }
    }

    /**
     * Counts and update the number of child processes
     */
    protected function _updateChildNum()
    {
        $this->_childNum = count($this->_childArray);
    }
    
    /**
     * fetches a request from the spool database and executes it
     */
    public function run()
    {
        $this->_startDatabase();

        while (!Runner::isDying()) {
            $this->_iterate(1);
            
            if ($this->_childs < 5 and !$this->_isChild) {
                $task = $this->_fetchNextRequest();
                if (!$task) {
                    continue;
                }
            }

            $this->_fork();

            if ($this->_isChild) {
                Action::factory($task->action)
                    ->execute();
                exit;
            }
        }
    }

    protected function _childCallBack()
    {
        
    }

    /**
     * updates the child processes count on susessfull fork
     *
     * @param <type> $pid 
     */
    protected function _parentCallBack($pid)
    {
        $this->_childArray[] = $pid;
        $this->_updateChildNum();
    }

    /**
     * @todo refactor this after migrate the whole db stuf
     */
    protected function _startDatabase()
    {
        if (!$this->_database instanceof Db) {
            $adapter = new Db_Adapter_Mongo();
            Db::setAdapter($adapter);
            $this->_database = new Db();
        }
    }

    /**
     * fetches the next request from the spool database
     *
     * @return Row
     */
    protected function _fetchNextRequest()
    {
        $order = array('priority' => 1, 'done' => -1);
        return $this->_database->fetchRow($order);
    }

}