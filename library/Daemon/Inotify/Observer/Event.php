<?php
namespace Daemon\Inotify\Observer;

use Daemon\Inotify\Watcher;

abstract class Event implements \SplObserver
{
    /**
     * holds instance of inotify watcher
     *
     * @var Watcher
     */
    protected $_inotify;

    /**
     * the beauty of magic constructor is that it can be extended by all child
     * classes
     */
    public function __construct()
    {
        $this->_inotify = Watcher::getInstance();
    }
}