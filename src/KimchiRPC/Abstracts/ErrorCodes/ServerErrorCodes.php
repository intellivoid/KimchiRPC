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

        /**
         * The request the client sent is malformed and couldn't be understood by the server
         */
        const MalformedRequestException = -5605;

        /**
         * The request method used in the http request is not supported
         */
        const UnsupportedHttpRequestMethodException = -5606;

        /**
         * The request made to the server is invalid and contains missing fields
         */
        const BadRequestException = -5607;
    }