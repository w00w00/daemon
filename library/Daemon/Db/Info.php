<?php
namespace Daemon\Db;

class Info
{
    private $_reflection;
    
    public function __construct(Row $row)
    {
        $this->_reflection = new \ReflectionObject($row);
    }

    public function getFields()
    {
        return $this->_reflection->getProperties();
    }

    public function getFieldInfo(\ReflectionProperty $property)
    {
        $doc = $property->getDocComment();
    }
}