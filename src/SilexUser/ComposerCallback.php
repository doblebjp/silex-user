<?php

namespace SilexUser;

use Composer\Script\Event;

class ComposerCallback
{
    public static function postInstall(Event $event)
    {
        $dataDir = realpath(__DIR__ . '/../../data');
        chmod($dataDir, 0777);
        $dbFile = $dataDir . '/app.db';
        touch($dbFile);
        chmod($dbFile, 0666);
    }
}
