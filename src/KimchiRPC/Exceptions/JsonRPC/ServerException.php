<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace KimchiRPC\Exceptions\JsonRPC;

    use Exception;
    use KimchiRPC\Interfaces\JsonRpcException;
    use KimchiRPC\Objects\JsonRPC\Error;
    use Throwable;

    /**
     * Class ServerException
     * @package KimchiRPC\Exceptions\JsonRPC
     */
    class ServerException extends Exception implements JsonRpcException
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * ServerException constructor.
         * @param string $message
         * @param int $code
         * @param Throwable|null $previous
         * @throws InternalErrorException
         */
        public function __construct($message = "", $code = 0, Throwable $previous = null)
        {
            if($code < -32000 || $code > -32099)
                throw new InternalErrorException("The error code for this exception is out of range for the standard", $previous);

            parent::__construct($message, $code, $previous);
            $this->message = $message;
            $this->code = $code;
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