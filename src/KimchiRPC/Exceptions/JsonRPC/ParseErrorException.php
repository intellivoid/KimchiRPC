<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace KimchiRPC\Exceptions\JsonRPC;

    use Exception;
    use KimchiRPC\Abstracts\ErrorCodes\JsonStandardErrorCodes;
    use KimchiRPC\Interfaces\JsonRpcException;
    use KimchiRPC\Objects\JsonRPC\Error;
    use Throwable;

    /**
     * Class ParseErrorException
     * @package KimchiRPC\Exceptions\JsonRPC
     */
    class ParseErrorException extends Exception implements JsonRpcException
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * ParseErrorException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, JsonStandardErrorCodes::ParseError, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }

        /**
         * @inheritDoc
         * @return Error
         */
        public function toErrorObject(): Error
        {
            return Error::fromException($this);
        }
    }