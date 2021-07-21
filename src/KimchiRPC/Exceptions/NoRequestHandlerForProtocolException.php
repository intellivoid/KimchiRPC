<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace KimchiRPC\Exceptions;

    use Exception;
    use Throwable;

    /**
     * Class NoRequestHandlerForProtocolException
     * @package KimchiRPC\Exceptions
     */
    class NoRequestHandlerForProtocolException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * NoRequestHandlerForProtocolException constructor.
         * @param string $message
         * @param int $code
         * @param Throwable|null $previous
         */
        public function __construct($message = "", $code = 0, Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
            $this->message = $message;
            $this->code = $code;
            $this->previous = $previous;
        }
    }