<?php
namespace Daemon\Inotify\Observer;

use Daemon\Inotify\EventHandler;

/**
 * Observer class for modified files
 */
class Moved extends Event
{

    /**
     * will listen to all file modification events
     *
     * @param \SplSubject $subject 
     */
    public function update(\SplSubject $subject)
    {
        foreach ($subject->getMoved() as $moved) {
            if ($moved[0]->isDir()
                and !$this->_inotify->isWatching($moved[0]->getPathname())) {
                $moved->update($moved[1]->getPathname());
            }
        }
    }
}