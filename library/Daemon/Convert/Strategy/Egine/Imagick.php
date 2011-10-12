<?php

namespace Classes\Strategy\Engine;

use Classes\Standard\IEngine,
    Classes\System\FileInfo;

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
    public function crop(Convert $config)
    {
        return $this;
    }

    /**
     * resizes the picture
     *
     * @param Convert $config
     * @return Imagick 
     */
    public function resize(Convert $config)
    {
        $this->_file->thumbnailImage(
            $config->getWidth(),
            $config->getHeight()
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
        $this->_file = new \Imagick($path->getPathInfo());

        return $this;
    }

    /**
     * creates a watermark on the picture
     *
     * @param Convert $config
     * @param FileInfo $mark 
     */
    public function watermark(Convert $config, FileInfo $mark)
    {
        
    }

}