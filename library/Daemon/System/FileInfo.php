<?php
namespace Daemon\System;

/**
 * Class to handle a file
 *
 * @author Jhonatan Teixeira
 */
class FileInfo extends \SplFileInfo
{
    /**
     * returns the filename without extension
     *
     * @return string
     */
    public function getFilename()
    {
        return pathinfo($this->getBasename(), PATHINFO_FILENAME);
    }

    /**
     * returns the file mimetype
     *
     * @return string
     * @see Daemon\System\MimeType::getMime
     */
    public function getMimeType()
    {
        if (!$this->exists()) {
            throw new Exception("can't get mime type from inexistent file");
        }

        return MimeType::getMime($this);
    }

    /**
     * returns the file extension
     *
     * @return string
     */
    public function getExtension()
    {
        return pathinfo($this->getBasename(), PATHINFO_EXTENSION);
    }

    /**
     * checks the file existence wheter if its a dir or a file
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->getPathname());
    }
}