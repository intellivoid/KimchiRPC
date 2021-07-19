<?php


    namespace KimchiRPC\Interfaces;


    use KimchiRPC\Objects\JsonRPC\Error;
    use Throwable;

    /**
     * Interface JsonRpcException
     * @package KimchiRPC\Interfaces
     */
    interface JsonRpcException extends Throwable
    {
        /**
         * Converts this exception to a Json RPC Error object
         *
         * @return Error
         */
        public function toErrorObject(): Error;
    }