<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace KimchiRPC\Exceptions\Server;

    use Exception;
    use KimchiRPC\Abstracts\ErrorCodes\ServerErrorCodes;
    use Throwable;

    /**
     * Class UnsupportedHttpRequestMethodException
     * @package KimchiRPC\Exceptions\Server
     */
    class UnsupportedHttpRequestMethodException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * UnsupportedHttpRequestMethodException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, ServerErrorCodes::UnsupportedHttpRequestMethodException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }