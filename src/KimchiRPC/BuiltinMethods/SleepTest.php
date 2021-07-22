<?php


    namespace KimchiRPC\BuiltinMethods;

    use Exception;
    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;

    /**
     * Class SleepTest
     * @package KimchiRPC\BuiltinMethods
     */
    class SleepTest implements MethodInterface
    {

        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return "Sleep Test";
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return "server.sleep_test";
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return "Tests the sleep handling";
        }

        /**
         * @inheritDoc
         */
        public function getVersion(): string
        {
            return "1.0.0.0";
        }

        /**
         * @inheritDoc
         */
        public function execute(Request $request): Response
        {
            sleep(3);

            $response = Response::fromRequest($request);
            $response->ResultData = "Slept for 3 seconds";

            return $response;
        }
    }