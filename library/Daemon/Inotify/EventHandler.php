<?php
namespace Daemon\Inotify;
use Daemon\Inotify\Observer\Event;

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
     * @var System_Inotify
     */
    protected $_inotify;

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
            $behaviors = new System_Inotify_DefaultBehaviors(
                $this->_inotify,
                $this
            );

            $behaviors->onCreate();
            $behaviors->onModify();
        } else {
            $this->_isEmpty = true;
        }

        $this->_events    = null;
        $this->_observers = new \SplObjectStorage();
    }

    /**
     *
     * @param <type> $event
     * @return System_FileHandler 
     */
    protected function _getFileName($event)
    {
        $descriptor = $this->_inotify->getDescriptor($event['wd']);
        $filename = $descriptor->getPathname()."/".$event['name'];

        return new System_FileHandler($filename);
    }

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

    protected function _filterNoName()
    {
        foreach ($this->_events as $key=>$event) {
            if (!$event['name']) {
                unset($this->_events[$key]);
            }
        }
    }

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

    public function getMoved()
    {
        return $this->_moved;
    }

    public function getRemoved()
    {
        return $this->_removed;
    }

    /**
     *
     * @return array
     */
    public function getCreated()
    {
        return $this->_created;
    }

    public function getModified()
    {
        return $this->_modified;
    }

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

    public function attach(\SplObserver $observer)
    {
        if (!$observer instanceof Event) {
            throw new Exception(
                'only subjects derived from Event is allowed'
            );
        }
        
        $this->_observers->attach($observer);
    }

    public function detach(\SplObserver $observer)
    {
        $this->_observers->detach($observer);
    }

    public function notify()
    {
        foreach ($this->_observers as $observer) {
            $observer->update($this);
        }
    }
}