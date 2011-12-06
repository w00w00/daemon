<?php
namespace Daemon\System;

class MimeType
{
    public static function getMime(\SplFileInfo $file)
    {
        $file = $file->getPathname();
        exec("mimetype $file", $result);
        $mime = trim(array_pop(explode(":", $result[0])));

        return $mime;
    }
}