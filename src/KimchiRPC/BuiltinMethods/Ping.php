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
         * @param Request $request
         * @return Response
         */
        public function execute(Request $request): Response
        {
            $response = new Response();
            $response->Success = true;
            $response->ResultData = true;

            return $response;
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
    }