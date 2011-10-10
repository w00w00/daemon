<?php
namespace Daemon\Convert;

use Daemon\Standard\IEngine;

use Daemon\Convert\Strategy\Engine,
    Daemon\Convert\Strategy\Context;

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

        $mime = $source->getMimeType();
        $className = "Classes\\Strategy\\" . str_replace("/", "\\", $mime);

        if (!class_exists($className, true)) {
            $className = preg_replace("/\/.*$/", "", $className);
        }

        $class = new $className($context);

        if (!$class instanceof  self) {
            throw new \Exception(
                "Strategy $className must be sub of Classes\Strategy"
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

        $className = "Engine\\{$this->_engineClassName}";

        $this->_engine = new $className();
        $this->_engine->setFile($context->getDestination());

        if (!$this->_engine instanceof IEngine) {
            throw new \RuntimeException(
                "Engine $className must be implement Classes\\Standard\\IEngine"
            );
        }

        $this->_context = $context;

        $this->_init();
    }

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