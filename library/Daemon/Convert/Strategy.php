<?php
namespace Daemon\Convert;

use Daemon\Standard\IEngine;

use Daemon\Convert\Strategy\Engine,
    Daemon\Convert\Strategy\Context;

/**
 * The strategy class selects convertion strategy wich best suits the source
 * and destination file, acording to the source mime type
 *
 * @author Jhonatan Teixeira
 */
abstract class Strategy
{
    /**
     * the engine class name must be set on the child classes
     *
     * @var string
     */
    protected $_engineClassName;

    /**
     *
     * @var IEngine
     */
    protected $_engine;

    /**
     * strategy context class instance
     *
     * @var Context
     */
    protected $_context;
    
    /**
     * the strategy factory, instantiates the right strategy class acording to
     * the params set on the convert class
     *
     * @param Context $context
     * @return Strategy
     */
    public static function factory(Context $context)
    {
        $source      = $context->getSource();
        $destination = $context->getDestination();

        $mime                  = $source->getMimeType();
        list($strategy, $type) = explode("/", $mime);
        $strategy              = ucfirst($strategy);
        $type                  = ucfirst($type);

        $className = "Daemon\\Convert\\Strategy\\$strategy\\$type";

        if (!class_exists($className, true)) {
            $className = preg_replace("/\/.*$/", "", $className);
        }

        $class = new $className($context);

        if (!$class instanceof  self) {
            throw new \Exception(
                "Strategy $className must be sub of Daemon\\Convert\\Strategy"
            );
        }

        return $class;
    }

    /**
     * the constuctor, receives the convert class, sets the engine adapter file
     * to work with and calls a fake constructor
     *
     * @param Context $context
     */
    final public function __construct(Context $context)
    {
        if (!isset($this->_engineClassName)) {
            throw new \UnexpectedValueException(
                'self::$_engineClassName name must be set,'
                . ' to setup the strategy engine'
            );
        }

        $className = "Daemon\\Convert\\Strategy\\Engine\\{$this->_engineClassName}";

        $this->_engine = new $className();
        $this->_engine->setFile($context->getSource());

        if (!$this->_engine instanceof IEngine) {
            throw new \RuntimeException(
                "Engine $className must implement Daemon\\Standard\\IEngine"
            );
        }

        $this->_context = $context;

        $this->_init();
    }

    /**
     * fake constructor, can be overriden by child classes
     */
    protected function _init()
    {
        
    }

    /**
     * returns the convert class set for this instance
     *
     * @return Context
     */
    final public function getConvert()
    {
        return $this->_context;
    }

    /**
     * a default conversion routine must be implemented for each strategy
     */
    abstract function defaultConversion();
}