<?php
namespace Daemon\Inotify;

use Daemon\System\FileInfo;

/**
 * This class uses the inotify php extension to watch the file system
 *
 * @author Jhonatan Teixeira
 */
class Watcher
{
    /**
     * resource opened by inotify_init
     *
     * @var resource
     */
    protected $_resource;

    /**
     * singleton holder
     *
     * @var Watcher
     */
    protected static $_instance;

    /**
     * array of descriptors caught so far
     *
     * @var array
     */
    protected $_descriptors = array();

    /**
     * array of folders marked as being watched
     *
     * @var array
     */
    protected $_isWatching = array();

    /**
     * Starts inotify engine
     */
    private function __construct()
    {
        $this->_resource = inotify_init();
    }

    private function __clone()
    {

    }

    /**
     * Implements sigleton
     *
     * @return Watcher
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Adds a watch to a file or directory
     *
     * @param string $file path to the desired watch target
     * @return Watcher
     */
    public function addWatch(FileInfo $file)
    {
        if ($file->exists()) {
            $descriptor = inotify_add_watch(
                $this->_resource,
                $file,
                IN_ALL_EVENTS
            );
            
            $this->_descriptors[$descriptor] = new Descriptor(
                $this->_resource, 
                $descriptor,
                $file
            );

            $this->_isWatching[$descriptor] = $file;

            System_Daemon::log(
                System_Daemon::LOG_INFO,
                "Started watching $file"
            );
        }

        return $this;
    }

    /**
     * Adiciona um watcher recursivo a um diretorio
     *
     * @param string $dir
     * @return Watcher
     */
    public function recursiveWatch(FileInfo $dir)
    {
        if ($dir->isDir()) {
            $this->addWatch($dir);

            $iterator = new \RecursiveDirectoryIterator(
                $dir->getPathname(),
                \RecursiveDirectoryIterator::CURRENT_AS_SELF
            );
            $recursive = new \RecursiveIteratorIterator(
                $iterator,
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($recursive as $file) {
                if (!$file->isDot() and $file->isDir())
                    $this->addWatch($file);
            }
        }

        return $this;
    }

    /**
     * checks wheter a file is aready being watched or not
     *
     * @param string $file
     * @return bool
     */
    public function isWatching(FileInfo $file)
    {
        return in_array($file->getPathname(), $this->_isWatching);
    }

    /**
     * Returns the watch descriptor of given file if it exists
     *
     * @param string $file
     * @return Descriptor 
     */
    public function getFileDescriptor(FileInfo $file)
    {
        if ($this->isWatching($file)) {
            $id = array_search($file->getPathname(), $this->_isWatching);
            return $this->getDescriptor($id);
        }

        return null;
    }

    /**
     * Atualiza um descriptor
     *
     * @param string $oldFile
     * @param string $newFile
     * @return Watcher
     */
    public function updateDescriptor(FileInfo $oldFile, FileInfo $newFile)
    {
        $descriptor = $this->getFileDescriptor($oldFile);

        if ($descriptor) {
            $descriptor->update($newFile);
        }

        System_Daemon::log(
            System_Daemon::LOG_INFO,
            "updated $oldFile to $newFile"
        );

        return $this;
    }

    /**
     * gets a watch descriptor by its id, false if id supplied doesn't exists
     *
     * @param int $id
     * @return Descriptor
     */
    public function getDescriptor($id)
    {
        if (isset($this->_descriptors[$id])) {
            return $this->_descriptors[$id];
        }

        return false;
    }

    /**
     * Stop watching a file name, does nothing in case it doesnt exists
     *
     * @param string $file
     * @return Watcher
     */
    public function removeWatchByFile($file)
    {
        $descriptor = $this->getFileDescriptor($file);

        if ($descriptor) {
            $descriptor->removeWatch();
        }

        return $this;
    }

    /**
     * Removes a watch by the supplied descriptor id, does nothing in case it
     * doesn't exists
     *
     * @param int $id id of a descriptor
     * @return Watcher
     */
    public function removeWatchByDescriptor($id)
    {
        if (isset($this->_descriptors[$id])) {
            $this->_descriptors[$id]->removeWatch();
        }

        return $this;
    }

    /**
     * Read the inotify events
     *
     * @return void
     */
    public function readEvents()
    {
        $handler = new EventHandler($this->_resource);

        $observerFiles = glob(__DIR__ . "/Observer/*.php");

        foreach ($observerFiles as $file) {
            if (preg_match("/Event.php$/", $file)) {
                continue;
            }

            $file = str_replace(".php", "", $file);

            $className = "Daemon\\Inotify\\Observer\\$file";
            $handler->attach(new $className());
        }

        $handler->notify();
    }
    
}