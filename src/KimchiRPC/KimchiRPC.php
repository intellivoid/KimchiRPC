<?php

    /** @noinspection PhpPropertyOnlyWrittenInspection */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiRPC;

    use BackgroundWorker\BackgroundWorker;
    use BackgroundWorker\Exceptions\UnexpectedTermination;
    use BackgroundWorker\Exceptions\WorkerException;
    use BackgroundWorker\Exceptions\WorkersAlreadyRunningException;
    use GearmanTask;
    use KimchiRPC\Abstracts\ServerMode;
    use KimchiRPC\Abstracts\Types\ProtocolType;
    use KimchiRPC\Exceptions\CannotHandleRequestException;
    use KimchiRPC\Exceptions\MethodAlreadyRegistered;
    use KimchiRPC\Exceptions\MissingComponentsException;
    use KimchiRPC\Exceptions\Server\BadRequestException;
    use KimchiRPC\Exceptions\Server\MethodNotFoundException;
    use KimchiRPC\Exceptions\Server\UnsupportedProtocolException;
    use KimchiRPC\Exceptions\ServerException;
    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;
    use KimchiRPC\Utilities\Converter;
    use KimchiRPC\Utilities\Helper;
    use RuntimeException;
    use Exception;
    use GearmanJob;
    use ZiProto\ZiProto;

    // TODO: Make server name function safe
    // TODO: Validate method names to be function safe

    // Define server information for response headers
    if(defined("KIMCHI_SERVER") == false)
    {
        if(file_exists(__DIR__ . DIRECTORY_SEPARATOR . "package.json") == false)
            throw new MissingComponentsException("The 'package.json' file was not found in the distribution");

        $package = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "package.json"), true);
        if($package == false)
            throw new RuntimeException("Cannot decode 'package.json', package components may be corrupted");

        define("KIMCHI_SERVER_VERSION", $package["package"]["version"]);
        define("KIMCHI_SERVER_ORGANIZATION", $package["package"]["organization"]);
        define("KIMCHI_SERVER_AUTHOR", $package["package"]["author"]);
        define("KIMCHI_SERVER", true);
    }

    /**
     * Class KimchiRPC
     * @package KimchiRPC
     */
    class KimchiRPC
    {
        /**
         * @var MethodInterface[]
         */
        private $methods;

        /**
         * @var string|ProtocolType
         */
        private $default_protocol;

        /**
         * @var bool
         */
        private $enable_background_worker;

        /**
         * @var string
         */
        private $server_name;

        /**
         * @var string|ServerMode
         */
        private $server_mode;

        /**
         * @var int
         */
        private $maximum_requests_per_batch;

        /**
         * @var BackgroundWorker
         */
        private $background_worker;

        /**
         * @var bool
         */
        private $worker_initialized;

        /**
         * @var bool
         */
        private $supervisor_initialized;

        /**
         * Server constructor.
         * @param string $server_name
         */
        public function __construct(string $server_name)
        {
            $this->methods = [];
            $this->server_name = Converter::functionNameSafe($server_name);
            $this->default_protocol = ProtocolType::JsonRpc2;
            $this->server_mode = ServerMode::Handler;
            $this->worker_initialized = false;
            $this->supervisor_initialized = false;
            $this->maximum_requests_per_batch = 0;
        }

        /**
         * Registers a method to the RPC server
         *
         * @param MethodInterface $method
         * @throws MethodAlreadyRegistered
         */
        public function registerMethod(MethodInterface $method)
        {
            if(isset($this->methods[$method->getMethod()]))
                throw new MethodAlreadyRegistered("The method '" . $method->getMethod() . "' is already registered");

            $this->methods[$method->getMethod()] = $method;
            $this->reorderMethods();
        }

        /**
         * Reorders the methods into alphabetical order
         */
        private function reorderMethods()
        {
            $method_reordered = array_keys($this->methods);
            sort($method_reordered);
            $methods_clean = [];

            foreach($method_reordered as $method_name)
            {
                if(is_int($method_name) == false)
                    $methods_clean[$method_name] = $this->methods[$method_name];
            }

            $this->methods = $methods_clean;
        }

        /**
         * Initializes the worker and registers all the registered functions
         */
        private function initializeWorker()
        {
            $rpc_server = $this;

            $this->getBackgroundWorker()->getWorker()->getGearmanWorker()->addFunction(
                $this->server_name, function(GearmanJob $job) use ($rpc_server)
                {
                    $request = Request::fromArray(ZiProto::decode($job->workload()));
                    $response = $rpc_server->executeMethod($request);

                    if($response == null)
                        return null;

                    return ZiProto::encode($response->toArray(true));
                }
            );
        }

        /**
         * Executes a method in the server and returns the response
         *
         * @param Request $request
         * @return Response
         */
        public function executeMethod(Request $request): ?Response
        {
            if($request->IsValidRequest == false)
                return Response::fromException($request->ProtocolType, $request->ID, new BadRequestException("Invalid Request"));

            // Avoid unsupported ID types
            if(Helper::isValidType($request->ID, ["string", "integer", "null"]) == false)
                return Response::fromException($request->ProtocolType, $request->ID, new BadRequestException("Invalid ID Type"));

            if(isset($this->methods[$request->Method]) == false)
            {
                // Ignore notification requests
                if(strtolower(gettype($request->ID)) == "null")
                    return null;

                $truncated_method = Converter::truncateString($request->Method, 20);
                return Response::fromException($request->ProtocolType, $request->ID, new MethodNotFoundException("The requested method '" . $truncated_method . "' was not found."));
            }

            try
            {
                return ($request->ID == null ? null : $this->methods[$request->Method]->execute($request));
            }
            catch(Exception $e)
            {
                return ($request->ID == null ? null : Response::fromException($request->ProtocolType, $request->ID, $e));
            }
        }

        /**
         * Handles the HTTP request and invokes the method(s) requested
         *
         * @return Response[]
         * @throws CannotHandleRequestException
         * @throws ServerException
         */
        public function handleRequest(): array
        {
            if($this->server_mode !== ServerMode::Handler)
                throw new ServerException("This instance's server mode is not a handler");

            if(http_response_code() == false)
                throw new CannotHandleRequestException("The method can only be invoked if the instance is running from a web server environment");

            try
            {
                $protocol = Helper::detectProtocol($this->getDefaultProtocol());
                $request_handler = Helper::getRequestHandler($protocol);
            }
            catch (UnsupportedProtocolException | Exceptions\NoRequestHandlerForProtocolException $e)
            {
                Helper::plainTextResponse(400, "The server does not support the requested RPC protocol");
                exit(1);
            }

            try
            {
                $requests = $request_handler->fromRequest($_SERVER["REQUEST_METHOD"]);

            }
            catch (Exception $e)
            {
                $request_handler->handleException($e, true);
                exit(1);
            }

            // Truncate the requests if it exceeds the amount of requests allowed
            if($this->maximum_requests_per_batch > 0 && count($requests) > $this->maximum_requests_per_batch)
            {
                $requests = array_slice($requests, 0, ($this->maximum_requests_per_batch - count($requests)));
            }

            $responses = [];

            if($this->enable_background_worker)
            {
                $this->getBackgroundWorker()->getClient()->getGearmanClient()->setCompleteCallback(
                    function(GearmanTask $task) use (&$responses)
                    {
                        if($task->data() !== null && strlen($task->data()) > 0)
                            $responses[] = Response::fromArray(ZiProto::decode($task->data()));
                    });

                foreach($requests as $request)
                {
                    if($request->BackgroundRequest)
                    {
                        $this->getBackgroundWorker()->getClient()->getGearmanClient()->doBackground(
                            $this->server_name, ZiProto::encode($request->toArray(true))
                        );
                    }
                    else
                    {
                        $this->getBackgroundWorker()->getClient()->getGearmanClient()->addTask(
                            $this->server_name, ZiProto::encode($request->toArray(true))
                        );
                    }

                }

                $this->getBackgroundWorker()->getClient()->getGearmanClient()->runTasks();
            }
            else
            {
                foreach($requests as $request)
                {
                    $response = $this->executeMethod($request);
                    if($response !== null)
                        $responses[] = $response;

                }
            }

            return $responses;
        }

        /**
         * Handles a response to the client
         *
         * @param Response[] $responses
         * @throws ServerException
         */
        public function handleResponses(array $responses)
        {
            if($this->server_mode !== ServerMode::Handler)
                throw new ServerException("This instance's server mode is not a handler");

            try
            {
                $protocol = Helper::detectProtocol($this->getDefaultProtocol());
                $request_handler = Helper::getRequestHandler($protocol);
            }
            catch (UnsupportedProtocolException | Exceptions\NoRequestHandlerForProtocolException $e)
            {
                Helper::plainTextResponse(400, "The server does not support the requested RPC protocol");
                exit(1);
            }

            $request_handler->handleResponse($responses);
        }

        /**
         * Handles the requests and gives the client a proper response
         *
         * @throws CannotHandleRequestException
         * @throws ServerException
         */
        public function handle()
        {
            if($this->server_mode !== ServerMode::Handler)
                throw new ServerException("This instance's server mode is not a handler");

            if(http_response_code() == false)
                throw new CannotHandleRequestException("The method can only be invoked if the instance is running from a web server environment");

            $this->handleResponses($this->handleRequest());
        }

        /**
         * Begins working and listening for incoming jobs
         *
         * @param bool $blocking
         * @param int $timeout
         * @param bool $throw_errors
         * @throws ServerException
         * @throws WorkerException
         */
        public function work(bool $blocking=true, int $timeout=500, bool $throw_errors=false)
        {
            if($this->server_mode !== ServerMode::Worker)
                throw new ServerException("This instance's server mode is not a worker");

            if($this->worker_initialized == false)
                $this->initializeWorker();

            $this->getBackgroundWorker()->getWorker()->work($blocking, $timeout, $throw_errors);
        }

        /**
         * Starts the service worker and begins to monitor the workers (Blocking)
         *
         * @param string $worker_path
         * @param int $instances
         * @throws ServerException
         * @throws UnexpectedTermination
         * @throws WorkersAlreadyRunningException
         */
        public function startService(string $worker_path, int $instances)
        {
            if($this->server_mode !== ServerMode::Service)
                throw new ServerException("This instance's server mode is not a service");

            //$this->initializeSupervisor();
            $this->getBackgroundWorker()->getSupervisor()->startWorkers($worker_path, $this->server_name, $instances);
            $this->getBackgroundWorker()->getSupervisor()->monitor_loop($this->server_name);
        }

        /**
         * Unregisters a method by name
         *
         * @param string $method
         */
        public function unregisterMethodByName(string $method)
        {
            if(isset($this->methods[$method]))
                unset($this->methods[$method]);
        }

        /**
         * Unregisters a method by method
         *
         * @param MethodInterface $method
         */
        public function unregisterMethodByMethod(MethodInterface $method)
        {
            $this->unregisterMethodByName($method->getMethod());
        }

        /**
         * Gets the default protocol used by the server
         *
         * @return ProtocolType|string
         */
        public function getDefaultProtocol()
        {
            return $this->default_protocol;
        }

        /**
         * Sets the default protocol
         *
         * @param ProtocolType|string $default_protocol
         */
        public function setDefaultProtocol($default_protocol): void
        {
            $this->default_protocol = $default_protocol;
        }

        /**
         * Determines if BackgroundWorker is enabled for this server
         *
         * @return bool
         */
        public function isEnableBackgroundWorker(): bool
        {
            if($this->enable_background_worker == null)
                return false;
            return $this->enable_background_worker;
        }

        /**
         * Enables BackgroundWorker to be used
         */
        public function enableBackgroundWorker(): void
        {
            $this->enable_background_worker = true;
        }

        /**
         * Disables BackgroundWorker
         */
        public function disableBackgroundWorker(): void
        {
            $this->enable_background_worker = false;
        }

        /**
         * @return string
         */
        public function getServerName(): string
        {
            return $this->server_name;
        }

        /**
         * Gets the current server mode that this process is running as
         *
         * @return ServerMode|string
         */
        public function getServerMode()
        {
            return $this->server_mode;
        }

        /**
         * Changes the server mode that this process is running as
         *
         * @param ServerMode|string $server_mode
         */
        public function setServerMode($server_mode): void
        {
            $this->server_mode = $server_mode;
        }

        /**
         * @return BackgroundWorker
         */
        public function getBackgroundWorker(): BackgroundWorker
        {
            if($this->background_worker == null)
                $this->background_worker = new BackgroundWorker();

            return $this->background_worker;
        }

        /**
         * @return MethodInterface[]
         */
        public function getRegisteredMethods(): array
        {
            return $this->methods;
        }

        /**
         * @return int
         */
        public function getMaximumRequestsPerBatch(): int
        {
            return $this->maximum_requests_per_batch;
        }

        /**
         * @param int $maximum_requests_per_batch
         */
        public function setMaximumRequestsPerBatch(int $maximum_requests_per_batch): void
        {
            $this->maximum_requests_per_batch = $maximum_requests_per_batch;
        }
    }