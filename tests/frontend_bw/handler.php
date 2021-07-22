<?php

    ini_set('display_errors', '1');
    require("ppm");

    ppm_import("net.intellivoid.kimchi_rpc");

    // Initialize the server as a handler
    $KimchiRPC = new \KimchiRPC\KimchiRPC("Kimchi Server");
    $KimchiRPC->setServerMode(\KimchiRPC\Abstracts\ServerMode::Handler);

    // Enable BackgroundWorker
    $KimchiRPC->enableBackgroundWorker();
    $KimchiRPC->getBackgroundWorker()->getClient()->addServer();

    // Register functions
    $KimchiRPC->registerMethod(new \KimchiRPC\BuiltinMethods\Ping()); // server.ping
    $KimchiRPC->registerMethod(new \KimchiRPC\BuiltinMethods\GetRegisteredMethods($KimchiRPC)); // server.get_registered_methods
    $KimchiRPC->registerMethod(new \KimchiRPC\BuiltinMethods\ExceptionTest()); // server.exception_test

    // Handle the requests and emits a response.
    $KimchiRPC->handle();