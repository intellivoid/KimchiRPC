# KimchiRPC

KimchiRPC is a multi-protocol RPC server that can handle a large workload of requests
efficiently using BackgroundWorker. You can create and register methods easily


## Compile and build

To compile and build KimchiRPC, use PPM or the Makefile that comes with this project
```sh
make clean update build
sudo make install

# sudo make install_fast -- Skips dependencies
```

## Method example

To write a method, use this template, you can also throw uncaught exceptions, and
the server will handle it natively. There is no absolute need to be tinkering with
the protocol itself, the server gives your method an Auto-Pilot approach.

```php
<?php


    namespace KimchiRPC\BuiltinMethods;

    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;

    /**
     * Class Ping
     * @package KimchiRPC\BuiltinMethods\Server
     */
    class Ping implements MethodInterface
    {
        /**
         * @inheritDoc
         * @return string
         */
        public function getMethodName(): string
        {
            return get_class($this);
        }

        /**
         * @inheritDoc
         * @return string
         */
        public function getMethod(): string
        {
            return "server.ping";
        }

        /**
         * @inheritDoc
         * @return string
         */
        public function getDescription(): string
        {
            return "Pings the RPC server";
        }

        /**
         * @inheritDoc
         * @return string
         */
        public function getVersion(): string
        {
            return "1.0.0.0";
        }

        /**
         * @param Request $request
         * @return Response
         */
        public function execute(Request $request): Response
        {
            $response = Response::fromRequest($request);
            $response->ResultData = true;

            return $response;
        }
    }
```

--------------------------------------------------------------------------------

## Non-Background Worker mode.

You can run KimchiRPC in non-background worker mode; be mind that in this way
the server has to execute each method one-by-one. For example; when executing 
5 sleep functions that sleeps for 5 seconds would result in the request taking 
up to 25 seconds to complete. When BackgroundWorker is enabled; this will take
5 seconds to complete since it executes all those methods in parallel.

Your handler is the file that gets executed whenever your server receives an
HTTP Request; this script should have full access to the HTTP request.

```php
<?php
    require("ppm");

    ppm_import("net.intellivoid.kimchi_rpc");

    // Initialize the server as a handler
    $KimchiRPC = new \KimchiRPC\KimchiRPC("Kimchi Server");
    $KimchiRPC->setServerMode(\KimchiRPC\Abstracts\ServerMode::Handler);

    // Register functions
    $KimchiRPC->registerMethod(new \KimchiRPC\BuiltinMethods\Ping()); // server.ping
    $KimchiRPC->registerMethod(new \KimchiRPC\BuiltinMethods\GetRegisteredMethods($KimchiRPC)); // server.get_registered_methods
    $KimchiRPC->registerMethod(new \KimchiRPC\BuiltinMethods\ExceptionTest()); // server.exception_test
    $KimchiRPC->registerMethod(new \KimchiRPC\BuiltinMethods\SleepTest()); // server.sleep_test

    // Handle the requests and emits a response.
    $KimchiRPC->handle();

```

--------------------------------------------------------------------------------

## BackgroundWorker Mode

BackgroundWorker enables the full performance potential of KimchiRPC but requires
additional configuration.

 - A service needs to be executed and running in the background; otherwise
   any request that the handler receives will stall and result in a timeout.
 - A worker program needs to be written; this is basically a handler for the
   service, this program does not need to be executed but this is what a 
   service component needs to execute.
 - A handler, which is designed to simply distribute the jobs to the workers
   via the service and return the results back.
   

### Diagram
```
                                         ┌──────────┐
                            ┌────────────┤          │
                            │            │  Worker  │
                            │  ┌─────────►          │
                            │  │         └──────────┘
┌───────────┐      ┌────────▼──┤
│           │      │           │         ┌──────────┐
│           ◄──────┤           ├─────────►          │
│  Handler  │      │  Service  │         │  Worker  │
│           ├──────►           ├─────────►          │
│           │      │           │         └──────────┘
└───────────┘      └─────────▲─┤
                             │ │         ┌──────────┐
                             │ └─────────►          │
                             │           │  Worker  │
                             └───────────┤          │
                                         └──────────┘
```

### Worker Program (worker.php)

This is the code that actually gets executed when the service sends a job it
receives from the handler. You do not run this script, the service does.

```php
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
    $KimchiRPC->registerMethod(new \KimchiRPC\BuiltinMethods\ExceptionTest()); // server.exception_test
    $KimchiRPC->registerMethod(new \KimchiRPC\BuiltinMethods\SleepTest()); // server.sleep_test

    // Start working
    $KimchiRPC->work();
```

### Service Program (service.php)

A service program is the main program to be executed and must be running 24/7,
BackgroundWorker has a builtin supervisor that will monitor the workers and make
sure that they are running.

```php
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
```

Execute the program as so
```sh
/usr/bin/php service.php
```


### Handler (handler.php)

This is the endpoint where all the HTTP requests are to be handled. This script
sends the jobs to the service and waits for all the jobs to complete before
sending the response back to the client, this program is only to be executed
by a webserver

```php
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

    // Handle the requests and emits a response.
    $KimchiRPC->handle();
```

--------------------------------------------------------------------------------

# Disclaimer of Warranty

Intellivoid Software is provided under this license on an "as is" basis, without
warranty of any kind either expressed, implied or statutory, including, without
limitation.

# License

Copyright (C) Intellivoid Technologies, - All Rights Reserved
Unauthorized copying of this file, via any medium is strictly prohibited
This software is Proprietary and confidential.