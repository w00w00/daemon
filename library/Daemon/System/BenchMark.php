<?php
namespace Daemon\System;

class BenchMark
{
    static protected $_start;
    static protected $_stats;

    static function start()
    {
        self::$_start = microtime(true);
        self::$_stats = new \stdClass();
    }

    static function getStats()
    {
        if (!self::$_start) {
            throw new \Exception('Benchmark not started');
        }

        self::$_stats->time   = number_format(
            microtime(true) - self::$_start,
            4
        );
        self::$_stats->memory = self::getUsedMemory();

        return self::$_stats;
    }

    static function getUsedMemory()
    {
        $value = memory_get_peak_usage(true);
        
        $unit = array('kb', 'mb', 'gb', 'tb');
        $counter = 0;
        while ($value > 1024) {
            $value /= 1024;
            $valueUnit = $unit[$counter++];
        }

        return number_format($value, 2) . " $valueUnit";
    }
}