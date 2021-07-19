<?php


    namespace KimchiRPC\Abstracts\ErrorCodes;

    /**
     * Class ServerErrorCodes
     * @package KimchiRPC\Abstracts\ErrorCodes
     */
    abstract class ServerErrorCodes
    {
        /**
         * Returns when all else fails and the exception was not properly caught
         */
        const InternalError = -5600;

        /**
         * When one ore more parameters are incorrect
         */
        const InvalidParametersException = -5601;

        /**
         * Method isn't registered on the server
         */
        const MethodNotFoundException = -5602;

        /**
         * One or more expected parameters are missing
         */
        const MissingParameterException = -5603;

        /**
         * The server doesn't support the requested protocol
         */
        const UnsupportedProtocol = -5604;
    }