<?php
namespace Daemon\Standard;

use Daemon\Convert\Strategy\Context,
    Daemon\System\FileInfo;

interface IEngine
{
    public function setFile(FileInfo $path);

    /**
     * @return IEngine
     */
    public function resize(Context $config);

    public function crop(Context $config);

    public function watermark(Context $config, FileInfo $mark);

    public function saveTo(FileInfo $path);
}