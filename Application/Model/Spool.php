<?php
namespace Application\Model;
use Daemon\Db\Row;

class Spool extends Row
{
    /**
     *
     * @var int
     * @primaryKey
     * @autoIncrement
     */
    public $idSpool;

    /**
     *
     * @var varchar
     * @size 200
     */
    public $actionNameSpool;

    /**
     *
     * @var text
     */
    public $paramsSpool;

    /**
     *
     * @var timestamp
     * @default currenttimestanp
     */
    public $createdSpool;

    /**
     *
     * @var timestamp
     */
    public $executedSpool;

    /**
     *
     * @var bool
     * @default 0
     */
    public $doneSpool;
}