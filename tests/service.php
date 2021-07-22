<?php

    require("ppm");

    ppm_import("net.intellivoid.kimchi_rpc");

    // Initialize the server as a worker
    \VerboseAdventure\VerboseAdventure::setStdout(true);
    $KimchiRPC = new \KimchiRPC\KimchiRPC("Kimchi Server");
    $KimchiRPC->setServerMode(\KimchiRPC\Abstracts\ServerMode::Service);
    $KimchiRPC->getBackgroundWorker()->getSupervisor()->addServer();
    $KimchiRPC->getBackgroundWorker()->getSupervisor()->setDisplayOutput(
        $KimchiRPC->getServerName(), true
    );

    // Start service
    $KimchiRPC->startService(__DIR__ . DIRECTORY_SEPARATOR . "worker.php", 50);