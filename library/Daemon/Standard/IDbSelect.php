<?php
namespace Daemon\Standard;

interface IDbSelect
{
    public function where();

    public function limit();

    public function order();

    public function fields();

    public function join($table, $type = 'left', array $fields = array());

    public function getString();
}