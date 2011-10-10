<?php
namespace Daemon\System;

class MimeType
{
    public static function getMime(\SplFileInfo $file)
    {
        $file = $file->getPathname();
        exec("minetype $file", $result);
        $mime = trim(array_pop(explode(":", $result)));

        return $mime;
    }
}