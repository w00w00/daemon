<?php
class Daemon_Watch extends Daemon
{
    /**
     *
     * @var System_Inotify
     */
    protected $_inotify;

    protected function _init()
    {
        $this->_inotify = System_Inotify::getInstance();
    }

    public function run()
    {
        $path = $this->_getArgument('-p', true);

        if (!$path) {
            throw new Exception('No path option, use the -p path option');
        }

        $this->_initWatch($path);

        while (!System_Daemon::isDying()) {
            $events = $this->_inotify->readEvents();

            if (!$events->isEmpty()) {
                foreach ($events->getCreated() as $created) {
                    if ($created->isDir())
                        $this->_initWatch($created->getPathname());
                }
                $events->log();
            }

            System_Daemon::iterate(1);
        }
    }

    protected function _initWatch($path)
    {
        $iterator = new System_FileIterator($path);
        $recursive = new RecursiveIteratorIterator(
            $iterator,
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($recursive as $file) {
            if (!$file->isDot() and $file->isDir())
                $file->watch();
        }
    }

    protected function _childCallBack()
    {

    }

    protected function _parentCallBack($pid)
    {

    }

    protected function _signalHandler($signal)
    {

    }
}