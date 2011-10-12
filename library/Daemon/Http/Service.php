<?php
namespace Daemon\Http;
/**
 * Services factory
 *
 * @author Jhonatan Teixeira
 */
abstract class Service
{
    protected $_request;
    protected $_requiredParams = array();

    /**
     * will try to factory a service right from the application namespace
     *
     * @param string $serviceName
     * @return Service 
     */
    static public function factory($serviceName)
    {
        $serviceName = ucfirst($serviceName);

        $className = "Application\\Service\\$serviceName";
        $service = new $className();

        if (!$service instanceof Service) {
            throw new UnexpectedValueException(
                'service must be subclass of Service'
            );
        }

        return $service;
    }

    /**
     * Validate the required params of the service
     */
    protected function _validateParams()
    {
        foreach ($this->_requiredParams as $param) {
            if (!$this->_request->getArg($param)) {
                throw new UnexpectedValueException("$param param is required");
            }
        }
    }

    /**
     * serve the service to the remote client
     *
     * @return <type> 
     */
    final public function serve()
    {
        $this->_request = Request::getInstance();
        $this->_validateParams();
        return $this->_serve();
    }

    abstract protected function _serve();
}