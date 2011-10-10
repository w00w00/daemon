<?php
namespace Daemon\Standard;

use Daemon\Convert,
    Daemon\System\FileInfo;

interface IEngine
{
    public function setFile(FileInfo $path);

    /**
     * @return IEngine
     */
    public function resize(Convert $config);

    public function crop(Convert $config);

    public function watermark(Convert $config, FileInfo $mark);

    public function saveTo(FileInfo $path);
}