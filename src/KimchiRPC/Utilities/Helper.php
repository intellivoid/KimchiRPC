<?php


    namespace KimchiRPC\Utilities;

    use KimchiRPC\Abstracts\ProtocolContentTypes;
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
            $request_headers = Helper::getRequestHeaders();

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

        /**
         * Sets the headers relating information about the server
         *
         * @param string $protocol
         */
        public static function setServerHeaders(string $protocol)
        {
            header("X-Powered-By: Kimchi-RPC Server v" . KIMCHI_SERVER_VERSION);
            header("X-Kimchi-Version: " . KIMCHI_SERVER_VERSION);
            header("X-Kimchi-Author: " . KIMCHI_SERVER_AUTHOR);
            header("X-Kimchi-Organization: " . KIMCHI_SERVER_ORGANIZATION);
            header("X-Protocol: " . $protocol);
        }

        /**
         * Returns a plain text response as a fallback
         *
         * @param int $response_code
         * @param string $message
         */
        public static function plainTextResponse(int $response_code, string $message)
        {
            http_response_code($response_code);
            header("Content-Type: " . ProtocolContentTypes::PlainText);
            Helper::setServerHeaders(ProtocolType::PlainText);
            print($message);
        }
    }