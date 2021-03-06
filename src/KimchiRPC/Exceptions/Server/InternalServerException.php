<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace KimchiRPC\Exceptions\Server;

    use Exception;
    use KimchiRPC\Abstracts\ErrorCodes\ServerErrorCodes;
    use Throwable;

    /**
     * Class InternalServerException
     * @package KimchiRPC\Exceptions\Server
     */
    class InternalServerException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InternalServerException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, ServerErrorCodes::InternalError, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }