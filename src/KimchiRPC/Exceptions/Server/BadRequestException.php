<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace KimchiRPC\Exceptions\Server;

    use Exception;
    use KimchiRPC\Abstracts\ErrorCodes\ServerErrorCodes;
    use Throwable;

    /**
     * Class BadRequestException
     * @package KimchiRPC\Exceptions\Server
     */
    class BadRequestException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * BadRequestException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, ServerErrorCodes::BadRequestException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }