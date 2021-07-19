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
     * Class MethodNotFoundException
     * @package KimchiRPC\Exceptions\JsonRPC
     */
    class MethodNotFoundException extends Exception implements JsonRpcException
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * MethodNotFoundException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, JsonStandardErrorCodes::MethodNotFound, $previous);
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