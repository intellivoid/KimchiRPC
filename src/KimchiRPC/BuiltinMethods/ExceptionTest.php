<?php


    namespace KimchiRPC\BuiltinMethods;


    use Exception;
    use KimchiRPC\Interfaces\MethodInterface;
    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;

    class ExceptionTest implements MethodInterface
    {

        /**
         * @inheritDoc
         */
        public function getMethodName(): string
        {
            return "Exception Test";
        }

        /**
         * @inheritDoc
         */
        public function getMethod(): string
        {
            return "server.exception_test";
        }

        /**
         * @inheritDoc
         */
        public function getDescription(): string
        {
            return "Tests the exception handling";
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
            throw new Exception("This is an example error", 100);
        }
    }