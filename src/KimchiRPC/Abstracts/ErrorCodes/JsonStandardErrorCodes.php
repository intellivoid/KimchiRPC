<?php


    namespace KimchiRPC\Abstracts\ErrorCodes;

    /**
     * Class JsonStandardErrorCodes
     * @package KimchiRPC\Abstracts\ErrorCodes
     *
     * The error codes from and including -32000 to -32768 are reserved for pre-defined errors.
     * Any code within this range, but not defined explicitly below is reserved for future use.
     * The error codes are nearly the same as those suggested for XML-RPC at the following url:
     * http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php
     */
    abstract class JsonStandardErrorCodes
    {
        /**
         * Invalid JSON was received by the server. An error occurred on the server while parsing the JSON text.
         */
        const ParseError = -32700;

        /**
         * The JSON sent is not a valid Request object.
         */
        const InvalidRequest = -32600;

        /**
         * The method does not exist / is not available.
         */
        const MethodNotFound = -32601;

        /**
         * Invalid method parameter(s).
         */
        const InvalidParams = -32602;

        /**
         * Internal JSON-RPC error.
         */
        const InternalError = -32603;

        /**
         * Reserved for implementation-defined server-errors.
         */
        const ServerError = "*";
    }