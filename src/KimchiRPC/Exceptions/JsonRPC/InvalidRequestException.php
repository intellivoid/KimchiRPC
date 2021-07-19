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
     * Class InvalidRequestException
     * @package KimchiRPC\Exceptions\JsonRPC
     */
    class InvalidRequestException extends Exception implements JsonRpcException
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidRequestException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, JsonStandardErrorCodes::InvalidRequest, $previous);
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