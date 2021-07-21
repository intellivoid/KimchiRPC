<?php


    namespace KimchiRPC\Interfaces;

    use KimchiRPC\Objects\Request;
    use KimchiRPC\Objects\Response;

    /**
     * Interface RequestHandlerInterface
     * @package KimchiRPC\Interfaces
     */
    interface RequestHandlerInterface
    {
        /**
         * Determines if the given input by the client is a request that contains more than one request
         *
         * @param array $data
         * @return bool
         */
        public function isBatchRequest(array $data): bool;

        /**
         * Parses the data and returns an array of standard requests created from the protocol request as a base
         *
         * @param array $data
         * @param bool $validate
         * @return Request[]
         */
        public function getQueries(array $data, bool $validate=true): array;

        /**
         * Prepares the input of standard responses and returns an array representation of the response representative of
         * the protocol being used in the response.
         *
         * @param Response[] $responses
         * @return array
         */
        public function prepareResponse(array $responses): array;

        /**
         * Parses the requested request method and returns an array of requests collected from the request
         *
         * @param string $request_method
         * @param array $options
         * @return Request[]
         */
        public function fromRequest(string $request_method, array $options=[]): array;
    }