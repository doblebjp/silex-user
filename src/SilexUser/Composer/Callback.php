<?php

namespace SilexUser\Composer;

use Composer\Script\Event;

class Callback
{
    public static function postInstall(Event $event)
    {
        $dataDir = realpath(__DIR__ . '/../../../data');
        chmod($dataDir, 0777);
        $dbFile = $dataDir . '/app.db';
        touch($dbFile);
        chmod($dbFile, 0666);
    }
}
