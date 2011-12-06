<?php
namespace Daemon\Db;

class Select
{
    protected $_from;

    protected $_fields = array('*');

    protected $_where;

    protected $_limit;

    protected $_order;

    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    public function from($table)
    {
        
    }

    public function fields(array $fields)
    {
        
    }

    public function where($condition, $value = null)
    {
        
    }

    public function limit($limit)
    {
        
    }

    public function order($field, $type = self::ORDER_ASC)
    {
        
    }
}