<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace KimchiRPC\Exceptions\Server;

    use Exception;
    use KimchiRPC\Abstracts\ErrorCodes\ServerErrorCodes;
    use Throwable;

    /**
     * Class InvalidParametersException
     * @package KimchiRPC\Exceptions\Server
     */
    class InvalidParametersException extends Exception
    {
        /**
         * @var Throwable|null
         */
        private ?Throwable $previous;

        /**
         * InvalidParametersException constructor.
         * @param string $message
         * @param Throwable|null $previous
         */
        public function __construct($message = "", Throwable $previous = null)
        {
            parent::__construct($message, ServerErrorCodes::InvalidParametersException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }