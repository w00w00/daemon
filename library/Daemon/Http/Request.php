<?php
namespace Daemon\Http;
/**
 * Http request abstraction
 *
 * @author Jhonatan Teixeira
 */
class Request
{
    protected $_baseUrl;
    protected $_action;
    protected $_get;
    protected $_post;
    protected static $_instance;

    /**
     * @return Http_Request
     */
    static public function getInstance()
    {
        if (!self::$_instance instanceof  self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    final private function __clone()
    {
        
    }

    /**
     * Parses the url recieved and build the object's values
     */
    final private function __construct()
    {
        //$this->appPath = APP_PATH;
        $this->_baseUrl = str_replace("index.php", "", $_SERVER['PHP_SELF']);

        $requestUri = str_replace("//", "/", $_SERVER['REQUEST_URI']);
        $request = str_replace($this->_baseUrl, "", $requestUri);
        $request = str_replace("?".$_SERVER['QUERY_STRING'], "", $request);

        $request = explode("/", $request);

        $this->_action = array_shift($request);
        if (!$this->_action) {
            $this->_action = "index";
        }

        $this->_get = new stdClass();
        while (count($request) > 0) {
            $arg = array_shift($request);
            $value = array_shift($request);

            $this->_get->$arg = $value;
        }

        $this->_post = new stdClass();
        foreach ($_POST as $key=>$value) {
            $this->_post->$key = $value;
        }

        foreach ($_GET as $key=>$value) {
            $this->_get->$key = $value;
        }
    }

    /**
     * Gets an argument from either $_GET or $_POST, if it fails returns the
     * value set on default
     *
     * @param mixed $name
     * @param mixed $default
     * @return mixed
     */
    public function getArg($name, $default = null)
    {
        if (isset($this->_get->$name)) {
            return $this->_get->$name;
        }

        if (isset($this->_post->$name)) {
            return $this->_post->$name;
        }

        return $default;
    }

    /**
     * Returns a value from $_POST if not exists returns the value from $default
     * if $name is not set returns entire post in object form
     *
     * @param mixed $name
     * @param mixed $default
     * @return mixed 
     */
    public function getPost($name = null, $default = null)
    {
        if (!$name) {
            return $this->_post;
        }

        if (isset($this->_post->$name)) {
            return $this->_post->$name;
        }

        return $default;
    }

    /**
     * Returns a value from $_GET if not exists returns the value from $default
     * if $name is not set returns entire get in object form
     * 
     * @param mixed $name
     * @param mixed $default
     * @return mixed
     */
    public function getGet($name = null, $default = null)
    {
        if (!$name) {
            return $this->_get;
        }

        if (isset($this->_get->$name)) {
            return $this->_get->$name;
        }

        return $default;
    }

    /**
     * gets the base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    /**
     * gets the action requested
     *
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }
}