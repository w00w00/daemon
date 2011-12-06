<?php
namespace Application\Action;

use Daemon\Action\Action;
use Daemon\System\FileInfo;
use Daemon\Convert\Strategy\Context;

/**
 * action de conversão de imagens
 *
 * @author Jhonatan Teixeira
 */
class ConvertImages extends Action
{
    /**
     * required params
     *
     * @var array
     */
    protected $_requiredParams = array('from', 'to');

    /**
     * try to convert an image using the strategy
     */
    protected function _execute()
    {
        $source      = $this->getParam('from');
        $destination = $this->getParam('to');

        $convert = new Context(
            new FileInfo($source),
            new FileInfo($destination)
        );

        $width  = $this->getParam('width');
        $height = $this->getParam('height');

        if ($width) {
            $convert->setWidth($width);
        }

        if ($height) {
            $convert->setHeight($height);
        }

        $convert->makeConversion();
    }
}