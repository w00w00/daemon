<?php
namespace Daemon\Db;

class Rowset implements \IteratorAggregate
{
    protected $_data = array();

    public function __construct(array $data, Table $table)
    {
        foreach ($data as $row) {
            $this->_data[] = $table->createRow($row);
        }
    }

    public function getIterator()
    {
        return \SplFixedArray::fromArray($this->_data);
    }
}