<?php
namespace Daemon\Inotify;
use Daemon\Inotify\Observer\Event,
    Daemon\System\FileHandler;

/**
 * The event handler is in fact an event organizer, it filters all events and
 * separate them on different stacks, it also is a SplSubject and implements an
 * observer pattern
 *
 * @todo Maybe this class should be a singleton for performance reasons
 * @author Jhonatan Teixeira
 */
class EventHandler implements \SplSubject
{
    protected $_events;

    protected $_isEmpty = false;

    protected $_moved = array();

    protected $_removed = array();

    protected $_created = array();

    protected $_modified = array();

    protected $_observers;

    /**
     *
     * @var Watcher
     */
    protected $_inotify;

    /**
     * will parse all events using the protected methods, then will start the
     * object storage to atach the event observers
     *
     * @param resource $resource needs a inotify extension resource to work
     */
    public function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new Exception('inotify resource expected');
        }

        $this->_events  = inotify_read($resource);
        $this->_inotify = Inotify::getInstance();

        if ($this->_events) {
            $this->_filterNoName();
            $this->_parseMoved();
            $this->_parseRemoved();
            $this->_parseModified();
            $this->_parseCreated();
        } else {
            $this->_isEmpty = true;
        }

        $this->_events    = null;
        $this->_observers = new \SplObjectStorage();
    }

    /**
     * returns a filename based on the file descriptor
     *
     * @param array $event should be an inotify event array
     * @return FileHandler
     */
    protected function _getFileName($event)
    {
        $descriptor = $this->_inotify->getDescriptor($event['wd']);
        $filename = $descriptor->getPathname()."/".$event['name'];

        return new FileHandler($filename);
    }

    /**
     * extracts from the event array all moved files
     */
    protected function _parseMoved()
    {
        $moved = array();
        foreach ($this->_events as $key=>$event) {
            $filename = $this->_getFileName($event);
            if ($event['cookie'] > 0) {
                $moved[$event['cookie']][] = $filename;
                unset($this->_events[$key]);
            }
        }

        $this->_moved = $moved;
    }

    /**
     * extracts from the event array all removed files
     */
    protected function _parseRemoved()
    {
        $removed = array();
        foreach ($this->_events as $key=>$event) {
            $filename = $this->_getFileName($event);

            if ($event['mask'] == IN_DELETE
                or $event['mask'] == IN_DELETE_SELF
                or !file_exists($filename)) {
                $removed[] = $filename;
                unset($this->_events[$key]);
            }
        }

        $this->_removed = $removed;
    }

    /**
     * extracts from the event array all modified files
     */
    protected function _parseModified()
    {
        foreach ($this->_events as $key=>$event) {
            $filename = $this->_getFileName($event);
            if ($event['mask'] == IN_MODIFY
                or $event['mask'] == IN_CLOSE_WRITE) {
               $this->_modified[] = $filename;
               unset($this->_events[$key]);
            }
        }
    }

    /**
     * extracts from the event array all created files
     */
    protected function _parseCreated()
    {
        foreach ($this->_events as $key=>$event) {
            $filename = $this->_getFileName($event);
            if (($event['mask'] == IN_CREATE
                or is_dir($filename))
                    and !$this->_inotify->isWatching($filename)) {
                $this->_created[] = $filename;
            }
        }
    }

    /**
     * removes from the event array all unamed files
     */
    protected function _filterNoName()
    {
        foreach ($this->_events as $key=>$event) {
            if (!$event['name']) {
                unset($this->_events[$key]);
            }
        }
    }

    /**
     * checks if events stacks are all empty
     *
     * @return <type> 
     */
    public function isEmpty()
    {
        if ($this->_isEmpty
            and !$this->_created
            and !$this->_modified
            and !$this->_moved
            and !$this->_removed) {
            return true;
        }

        return false;
    }

    /**
     * return parsed moved files
     *
     * @return array 
     */
    public function getMoved()
    {
        return $this->_moved;
    }

    /**
     * return parsed removed files
     *
     * @return array
     */
    public function getRemoved()
    {
        return $this->_removed;
    }

    /**
     * return parsed created files
     *
     * @return array
     */
    public function getCreated()
    {
        return $this->_created;
    }

    /**
     * return parsed modified files
     *
     * @return array
     */
    public function getModified()
    {
        return $this->_modified;
    }

    /**
     * Class logger
     */
    public function log()
    {
        ob_start();
        print_r($this->_moved);
        $moved = ob_get_clean();

        ob_start();
        print_r($this->_removed);
        $removed = ob_get_clean();

        ob_start();
        print_r($this->_created);
        $created = ob_get_clean();

        ob_start();
        print_r($this->_modified);
        $modified = ob_get_clean();

        $log = "Moved:\n$moved\n".
            "Removed:\n$removed\n".
            "Created:\n$created\n".
            "Modified:\n$modified\n";
        
        System_Daemon::log(System_Daemon::LOG_INFO, $log);
    }

    /**
     * Attach an observer to the class
     *
     * @param \SplObserver $observer 
     */
    public function attach(\SplObserver $observer)
    {
        if (!$observer instanceof Event) {
            throw new Exception(
                'only subjects derived from Event is allowed'
            );
        }
        
        $this->_observers->attach($observer);
    }

    /**
     * detach an observer
     *
     * @param \SplObserver $observer 
     */
    public function detach(\SplObserver $observer)
    {
        $this->_observers->detach($observer);
    }

    /**
     * executes the observers
     */
    public function notify()
    {
        foreach ($this->_observers as $observer) {
            $observer->update($this);
        }
    }
}