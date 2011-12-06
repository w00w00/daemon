<?php

set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            realpath(__DIR__ . "/../library"),
            get_include_path()
        )
    )
);

require_once 'Daemon/System/Autoloader.php';
Daemon\System\Autoloader::start();

$rest = new \Daemon\Rest\Server();
$rest->execute();