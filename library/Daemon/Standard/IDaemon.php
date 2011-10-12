<?php
namespace Daemon\Standard;

use Daemon\Dispatcher;

interface IDaemon
{
    /**
     * Contructor must have been implemented with dispatcher
     */
    public function setDispatcher(Dispatcher $dispatcher);

    /**
     * must implement a signal handler
     *
     * @param int $signal
     */
    protected function _signalHandler($signal);

    /**
     * Runs the daemon, run logic
     */
    public function run();

    /**
     * Must have a forking process
     */
    protected function _fork();

    /**
     * must implement what to do on the child at fork time
     */
    protected function _childCallBack();

    /**
     * must implement what to do on the parent at fork time
     */
    protected function _parentCallBack($pid);
}