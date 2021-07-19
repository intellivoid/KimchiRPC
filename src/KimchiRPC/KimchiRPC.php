<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiRPC;

    use BackgroundWorker\BackgroundWorker;
    use BackgroundWorker\Exceptions\UnexpectedTermination;
    use BackgroundWorker\Exceptions\WorkerException;
    use BackgroundWorker\Exceptions\WorkersAlreadyRunningException;
    use GearmanJob;
    use KimchiRPC\Abstracts\ServerMode;
    use KimchiRPC\Abstracts\Types\ProtocolType;
    use KimchiRPC\Exceptions\MethodAlreadyRegistered;
    use KimchiRPC\Exceptions\ServerException;
    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Utilities\Converter;
    use PpmZiProto\ZiProto;

    // TODO: Make server name function safe
    // TODO: Validate method names to be function safe

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
            $this->server_mode = ServerMode::Handler;
            $this->worker_initialized = false;
            $this->supervisor_initialized = false;
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

            foreach($method_reordered as $method_name)
                $method_reordered[$method_name] = $this->methods[$method_name];

            $this->methods = $method_reordered;
        }

        /**
         * Initializes the worker and registers all the registered functions
         */
        private function initializeWorker()
        {
            foreach($this->methods as $method_name => $method)
            {
                $this->getBackgroundWorker()->getWorker()->getGearmanWorker()->addFunction(
                    $this->server_name . "." . $method_name, function(GearmanJob $job) use ($method)
                    {
                        return ZiProto::encode($method->execute(Request::fromArray(ZiProto::decode($job->workload())))->toArray());
                    }
                );
            }
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
            return $this->enable_background_worker;
        }

        /**
         *
         *
         * @param bool $enable_background_worker
         */
        public function setEnableBackgroundWorker(bool $enable_background_worker): void
        {
            $this->enable_background_worker = $enable_background_worker;
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
    }