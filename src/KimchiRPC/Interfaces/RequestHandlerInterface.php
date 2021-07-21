<?php


    namespace KimchiRPC\Interfaces;

    use Exception;
    use KimchiRPC\Exceptions\Server\BadRequestException;
    use KimchiRPC\Exceptions\Server\MalformedRequestException;
    use KimchiRPC\Exceptions\Server\UnsupportedHttpRequestMethodException;
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
         * @throws Exception
         */
        public function fromRequest(string $request_method, array $options=[]): array;

        /**
         * Handles the HTTP response and sets the appropriate headers for the client
         *
         * @param Response[] $responses
         * @return mixed
         */
        public function handleResponse(array $responses);

        /**
         * Handles an exception and returns a response of the exception
         *
         * @param Exception $e
         * @param bool $suppress_non_internal Suppresses non-internal errors to a generic server exception error
         * @return mixed
         */
        public function handleException(Exception $e, bool $suppress_non_internal=false);
    }