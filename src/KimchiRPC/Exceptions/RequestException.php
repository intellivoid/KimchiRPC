<?php


    namespace KimchiRPC\Exceptions;

    use Exception;
    use Throwable;

    /**
     * Class RequestException
     * @package KimchiRPC\Exceptions
     */
    class RequestException extends Exception
    {
        public function __construct($message = "", $code = 0, Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }