<?php
namespace Daemon\Inotify\Observer;

use Daemon\Inotify\EventHandler;

/**
 * Observer class for deleted files
 */
class Created extends Event
{

    /**
     * will listen to all file deletion events
     *
     * @param \SplSubject $subject 
     */
    public function update(\SplSubject $subject)
    {
        
    }
}