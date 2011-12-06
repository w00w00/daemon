<?php
namespace Daemon\Rest;

use Daemon\Http\Request;
use Daemon\Http\Service;
use Daemon\System\BenchMark;

class Server
{
    protected $_request;

    public function __construct()
    {
        $this->_request = Request::getInstance();
    }

    /**
     * Executes a service class and return its response as xml
     */
    public function execute()
    {
        BenchMark::start();
        
        try {
            $actionName = $this->_request->getAction();
            $service = Service::factory($actionName);
            
            $response = $service->serve();

            if (!$response) {
                throw new Exception('Service returned no response');
            }

            $result = array(
                'status' => 'success',
                'response' => $response,
                'stats' => BenchMark::getStats()
            );
        } catch (Exception $e) {
            $result = array(
                'status' => 'error',
                'response' => $e->getMessage(),
                'stats' => BenchMark::getStats()
            );
        }

        header('Content-Type:text/xml');
        echo $this->_buildXml($result);
    }

    /**
     * builds an xml based on the result passed
     *
     * @param mixed $result
     * @return string - xml
     */
    protected function _buildXml($result)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $response = $dom->createElement('response');
        $dom->appendChild($response);

        $xml = simplexml_import_dom($dom);
        $xml->addChild('status', $result['status']);

        if (is_scalar($result['response'])) {
            $xml->addChild('result', $result['response']);

            return $xml->asXML();
        }

        $xmlResult = $xml->addChild('result');

        foreach ($result['response'] as $key=>$value) {
            if (is_scalar($value)) {
                $xmlResult->addChild(is_numeric($key) ? 'key' : $key, $value);
            } else {
                $this->_handleSubResult($key, $value, $xmlResult);
            }
        }

        $this->_handleSubResult('stats', $result['stats'], $xml);

        return $xml->asXML();
    }

    /**
     * gets and an array or object and append as childs elements of the xml
     * object passed, works recursively for multidimensional values
     *
     * @param string $name
     * @param mixed $values
     * @param SimpleXMLElement $xml 
     */
    protected function _handleSubResult($name, $values, \SimpleXMLElement $xml)
    {
        $child = $xml->addChild(is_numeric($name) ? 'row' : $name);
        
        foreach ($values as $key=>$value) {
            if (is_scalar($value)) {
                $child->addChild(is_numeric($key) ? 'key' : $key, $value);
            } else {
                $this->_handleSubResult($key, $value, $child);
            }
        }
    }
}