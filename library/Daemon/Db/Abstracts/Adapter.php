<?php
namespace Daemon\Db\Abstracts\Adapter;

use Daemon\Db\Select;
use Daemon\Db\Rowset;

abstract class Adapter
{
    protected $_sql;

    abstract public function connect();

    abstract public function disconnect();

    abstract protected function _execute();

    abstract protected function _parseWhere(Select $select);

    abstract protected function _parseFields(Select $select);

    abstract protected function _parseLimit(Select $select);

    abstract protected function _parseOrder(Select $select);

    public function fetch(Select $select, $isRow = false)
    {
        $this->_parseFields($select);
        $this->_parseWhere($select);
        $this->_parseOrder($select);
        $this->_parseLimit($select);

        $result = $this->_execute();

        return $result ?: null;
    }
}