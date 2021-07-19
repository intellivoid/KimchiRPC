<?php

    require("ppm");

    ppm_import("net.intellivoid.kimchi_rpc");

    // Initialize the server as a worker
    $KimchiRPC = new \KimchiRPC\KimchiRPC("Kimchi Server");
    $KimchiRPC->setServerMode(\KimchiRPC\Abstracts\ServerMode::Worker);
    $KimchiRPC->getBackgroundWorker()->getWorker()->addServer();

    // Register functions
    $KimchiRPC->registerMethod(new \KimchiRPC\BuiltinMethods\Ping()); // server.ping
    $KimchiRPC->registerMethod(new \KimchiRPC\BuiltinMethods\GetRegisteredMethods($KimchiRPC)); // server.get_registered_methods

    // Start working
    $KimchiRPC->work();