<?php
namespace Daemon\Inotify;
use Daemon\System\FileInfo;
/**
 * Class used by inotify to represent a watch descriptor, it stores the spl
 * file info of the determined file being watched making the extension more
 * usable
 * 
 * @author Jhonatan Teixeira
 */
class Descriptor
{
    protected $_descriptor;
    protected $_inotifyRs;

    /**
     *
     * @var SplFileInfo
     */
    protected $_file;
    
    function  __construct($inotifyRs, $descriptor, FileInfo $file)
    {
        $this->_descriptor = $descriptor;
        $this->_inotifyRs = $inotifyRs;
        $this->update($file);
    }

    function  __call($name, $arguments)
    {
        return call_user_func(array($this->_file, $name));
    }

    function removeWatch()
    {
        inotify_remove_watch($this->_inotifyRs, $this->_descriptor);
    }

    function update(FileInfo $file)
    {
        $this->_file = $file;
    }
}