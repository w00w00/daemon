<?php
namespace Daemon\Convert\Strategy;

use Daemon\Convert\Strategy,
    Daemon\Convert\Strategy\Engine,
    Daemon\Standard\IEngine;

class Image extends Strategy
{
    protected $_engineClassName = 'Imagick';

    /**
     * the image default convertion
     */
    public function defaultConversion()
    {
        $this->_engine->resize($this->_context)
            ->saveTo($this->_context->getDestination());
    }
}