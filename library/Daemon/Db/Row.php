<?php
namespace Daemon\Db;

abstract class Row implements \IteratorAggregate
{
    public function __construct(array $data = null)
    {
        if ($data) {
            $this->fromArray($data);
        }
    }

    final public function getIterator()
    {
        return new \ArrayIterator($this);
    }

    final public function __set($name, $value)
    {
        Throw new \Exception('impossible to set inexistent attribute');
    }

    final public function fromArray(array $data)
    {
        foreach ($data as $field => $value) {
            $this->$field = $value;
        }
    }
}