<?php
namespace Daemon\Inotify\Observer;

use Daemon\Inotify\EventHandler;

/**
 * Observer class for created files
 */
class Created extends Event
{
    /**
     * will listen to all file creation events
     *
     * @param \SplSubject $subject 
     */
    public function update(\SplSubject $subject)
    {
        foreach ($subject->getCreated() as $created) {
            if ($created->isDir()
                and !$this->_inotify->isWatching($created->getPathname())) {
                $this->_inotify->recursiveWatch($created->getPathname());
            }
        }
    }
}