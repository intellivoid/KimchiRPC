<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpPropertyOnlyWrittenInspection */

    namespace KimchiRPC\Exceptions\Server;

    use Exception;
    use KimchiRPC\Abstracts\ErrorCodes\ServerErrorCodes;
    use Throwable;

    /**
     * Class MethodNotFoundException
     * @package KimchiRPC\Exceptions\Server
     */
    class MethodNotFoundException extends Exception
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
            parent::__construct($message, ServerErrorCodes::MethodNotFoundException, $previous);
            $this->message = $message;
            $this->previous = $previous;
        }
    }