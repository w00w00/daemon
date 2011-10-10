<?php
namespace Daemon\Convert\Strategy;

use Daemon\System\FileInfo,
    Daemon\Convert\Strategy;

class Convert
{
    /**
     * source file info class
     *
     * @var FileInfo
     */
    protected $_source;

    /**
     * destination file info class
     *
     * @var FileInfo
     */
    protected $_destination;

    /**
     * width of destination file
     *
     * @var float
     */
    protected $_width;

    /**
     * height of destination file
     *
     * @var float
     */
    protected $_height;

    /**
     * constructor, can set destination and source for the class
     *
     * @param FileInfo $source
     * @param FileInfo $destination 
     */
    public function __construct(FileInfo $source = null,
        FileInfo $destination = null)
    {
        if ($source)
            $this->setSource($source);

        if ($destination)
            $this->setDestination($destination);
    }

    /**
     * sets the source file class
     *
     * @param FileInfo $source
     * @return Convert
     */
    public function setSource(FileInfo $source)
    {
        $this->_source = $source;

        return $this;
    }

    /**
     * sets the destination file class
     *
     * @param FileInfo $destination
     * @return Convert
     */
    public function setDestination(FileInfo $destination)
    {
        $this->_destination = $destination;

        return $this;
    }

    /**
     * set the width
     *
     * @param float $width
     * @return Convert 
     */
    public function setWidth($width)
    {
        $this->_width = (int) $width;
        return $this;
    }

    /**
     * set the height
     *
     * @param float $height
     * @return Convert 
     */
    public function setHeight($height)
    {
        $this->_height = (int) $height;
        return $this;
    }

    /**
     * returns the sourc file class
     *
     * @return FileInfo
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * returns the destination file class
     *
     * @return FileInfo
     */
    public function getDestination()
    {
        return $this->_destination;
    }

    /**
     * returns the height
     *
     * @return float
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * returns the width
     *
     * @return float
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * facilitates the convertion by calling the strategy factory passing itself
     * if the method for the conversion exists itll be called, defaultConversion
     * will be used otherwise
     *
     * @return mixed the conversion result
     */
    public function makeConversion()
    {
        $methodName = $this->_destination->getExtension();
        $strategy = Strategy::factory($this);

        if (!method_exists($strategy, $methodName)) {
            $result = $strategy->defaultConversion();
        } else {
            $result = $strategy->$methodName();
        }

        return $result;
    }

}