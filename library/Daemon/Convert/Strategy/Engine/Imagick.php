<?php

namespace Daemon\Convert\Strategy\Engine;

use Daemon\Standard\IEngine,
    Daemon\System\FileInfo,
    Daemon\Convert\Strategy\Context;

class Imagick implements IEngine
{

    /**
     *
     * @var \Imagick
     */
    protected $_file;

    /**
     * crops the image
     *
     * @param Convert $config
     * @return <type> 
     */
    public function crop(Context $config)
    {
        return $this;
    }

    /**
     * resizes the picture
     *
     * @param Convert $config
     * @return Imagick 
     */
    public function resize(Context $config)
    {
        $this->_file->thumbnailImage(
            $config->getWidth(),
            $config->getHeight(),
            true
        );

        return $this;
    }

    /**
     * saves the picture
     *
     * @param FileInfo $path 
     */
    public function saveTo(FileInfo $path)
    {
        $this->_file->writeImage($path->getPathname());
    }

    /**
     * sets the picture file
     *
     * @param FileInfo $path
     * @return Imagick 
     */
    public function setFile(FileInfo $path)
    {
        $this->_file = new \Imagick($path->getPathname());

        return $this;
    }

    /**
     * creates a watermark on the picture
     *
     * @param Convert $config
     * @param FileInfo $mark 
     */
    public function watermark(Context $config, FileInfo $mark)
    {
        
    }

}