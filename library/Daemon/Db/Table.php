<?php
namespace Daemon\Db;
use Daemon\Db\Abstracts\Adapter;

abstract class Table
{
    /**
     *
     * @var Adapter
     */
    protected static $_defaultAdapter;

    /**
     *
     * @var Adapter
     */
    protected $_adapter;

    protected $_tableName;

    protected $_rowClass;

    public static function setDefaultAdapter(Adapter $adapter)
    {
        self::$_defaultAdapter = $adapter;
    }

    /**
     *
     * @return Adapter
     */
    public static function getDefaultAdapter()
    {
        return self::$_defaultAdapter;
    }

    public function __construct()
    {
        $this->_setupTableName();
        $this->_setupRowClass();
    }

    protected function _setupTableName()
    {
        $reflection = new \ReflectionObject($this);
        $className = $reflection->getShortName();
        $this->_tableName = \strtolower($className);
    }

    protected function _setupRowClass()
    {
        $reflection = new \ReflectionObject($this);
        $className = $reflection->getShortName();
        $this->_rowClass = "Application\\Model\\$className";
    }

    /**
     *
     * @return Adapter
     */
    public function getAdapter()
    {
        if (isset($this->_adapter)) {
            return $this->_adapter;
        }

        return self::$_defaultAdapter;
    }

    public function setAdapter(Adapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    public function getConnection()
    {
        $this->getAdapter()->connect();
    }

    public function disconnect()
    {
        $this->getAdapter()->disconnect();
    }

    public function fetchAll()
    {

    }

    public function fetchRow()
    {
        
    }

    public function createRow(array $data = null)
    {
        return new $this->_rowClass($data);
    }
}