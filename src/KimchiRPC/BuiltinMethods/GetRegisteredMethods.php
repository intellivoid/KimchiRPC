<?php


    namespace KimchiRPC\BuiltinMethods;

    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\KimchiRPC;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;

    /**
     * Class GetRegisteredMethods
     * @package KimchiRPC\BuiltinMethods
     */
    class GetRegisteredMethods implements MethodInterface
    {
        /**
         * @var KimchiRPC
         */
        private KimchiRPC $kimchiRPC;

        /**
         * GetRegisteredFunctions constructor.
         * @param KimchiRPC $kimchiRPC
         */
        public function __construct(KimchiRPC $kimchiRPC)
        {
            $this->kimchiRPC = $kimchiRPC;
        }

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
            return "server.get_registered_methods";
        }

        /**
         * @inheritDoc
         * @return string
         */
        public function getDescription(): string
        {
            return "Returns an array of registered functions on the server";
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
         * @inheritDoc
         * @param Request $request
         * @return Response
         */
        public function execute(Request $request): Response
        {
            $response = new Response();
            $response->Success = true;
            $response->ResultData = array_keys($this->kimchiRPC->getRegisteredMethods());

            return $response;
        }
    }