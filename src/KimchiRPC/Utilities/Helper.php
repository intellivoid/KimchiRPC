<?php


    namespace KimchiRPC\Utilities;

    use KimchiRPC\Abstracts\Types\ProtocolType;
    use KimchiRPC\Abstracts\Types\SupportedContentTypes;
    use KimchiRPC\Classes\RequestHandlers\JsonRpcRequestHandler;
    use KimchiRPC\Exceptions\NoRequestHandlerForProtocolException;
    use KimchiRPC\Exceptions\Server\UnsupportedProtocolException;
    use KimchiRPC\Interfaces\RequestHandlerInterface;

    /**
     * Class Helper
     * @package KimchiRPC\Utilities
     */
    class Helper
    {
        /**
         * Returns an array of headers from the HTTP request
         *
         * @return array
         */
        public static function getRequestHeaders(): array
        {
            if(function_exists('getallheaders'))
                return getallheaders();

            $results = [];

            foreach($_SERVER as $key=>$value)
            {
                if (substr($key,0,5) == "HTTP_")
                {
                    $key= str_replace(" ","-", ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
                    $results[$key]=$value;

                }
                else
                {
                    $results[$key]=$value;
                }
            }

            return $results;
        }

        /**
         * Returns the request handler for the specified protocol
         *
         * @param string $protocol
         * @return RequestHandlerInterface
         * @throws NoRequestHandlerForProtocolException
         */
        public static function getRequestHandler(string $protocol): RequestHandlerInterface
        {
            switch($protocol)
            {
                case ProtocolType::JsonRpc2:
                    return new JsonRpcRequestHandler();

                default:
                    throw new NoRequestHandlerForProtocolException("There is no request handler for '$protocol'");
            }
        }

        /**
         * Detects the protocol that the client is requesting to use
         *
         * @param string $default_protocol
         * @return string
         * @throws UnsupportedProtocolException
         */
        public static function detectProtocol(string $default_protocol): string
        {
            if(isset($request_headers["Content-Type"]))
            {
                switch($request_headers["Content-Type"])
                {
                    case SupportedContentTypes::JsonRpc:
                    case SupportedContentTypes::JsonRpc2:
                    case SupportedContentTypes::JsonRpc3:
                        return ProtocolType::JsonRpc2;

                    default:
                        throw new UnsupportedProtocolException("The server does not support the requested protocol");
                }
            }

            return $default_protocol;
        }
    }