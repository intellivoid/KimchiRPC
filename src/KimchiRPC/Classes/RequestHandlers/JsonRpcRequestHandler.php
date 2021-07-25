<?php


    namespace KimchiRPC\Classes\RequestHandlers;


    use Exception;
    use KimchiRPC\Abstracts\ErrorCodes\JsonStandardErrorCodes;
    use KimchiRPC\Abstracts\ErrorCodes\ServerErrorCodes;
    use KimchiRPC\Abstracts\ProtocolContentTypes;
    use KimchiRPC\Abstracts\Types\ProtocolType;
    use KimchiRPC\Abstracts\Types\RequestMethod;
    use KimchiRPC\Abstracts\Types\SupportedContentTypes;
    use KimchiRPC\Exceptions\JsonRPC\InvalidRequestException;
    use KimchiRPC\Exceptions\Server\BadRequestException;
    use KimchiRPC\Exceptions\Server\MalformedRequestException;
    use KimchiRPC\Exceptions\Server\UnsupportedHttpRequestMethodException;
    use KimchiRPC\Interfaces\RequestHandlerInterface;
    use KimchiRPC\Objects\JsonRPC\Request;
    use KimchiRPC\Objects\JsonRPC\Response;
    use KimchiRPC\Utilities\Helper;

    /**
     * Class JsonRPC
     * @package KimchiRPC\Classes\RequestHandlers
     */
    class JsonRpcRequestHandler implements RequestHandlerInterface
    {
        /**
         * @inheritDoc
         * @param array $data
         * @return bool
         */
        public function isBatchRequest(array $data): bool
        {
            foreach($data as $datum)
            {
                if(gettype($datum) !== "array")
                    return false;

                try
                {
                    $r = Request::fromArray($datum);
                    $r->validate();
                }
                catch(InvalidRequestException $e)
                {
                    return false;
                }
            }

            return true;
        }

        /**
         * @inheritDoc
         * @param array $data
         * @param bool $validate
         * @return array
         * @throws InvalidRequestException
         * @noinspection DuplicatedCode
         */
        public function getQueries(array $data, bool $validate = true): array
        {
            /** @var Request[] $results */
            $results = [];
            $client_ip = Helper::getClientIP();

            if($this->isBatchRequest($data))
            {
                foreach($data as $datum)
                {
                    $results[] = \KimchiRPC\Objects\Request::fromJsonRpcRequest(Request::fromArray($datum), $client_ip);
                }

                if($validate)
                {
                    foreach($results as $request)
                        $request->ProtocolRequestObject->validate();
                }

            }
            else
            {
                $r = \KimchiRPC\Objects\Request::fromJsonRpcRequest(Request::fromArray($data), $client_ip);
                if($validate)
                    $r->ProtocolRequestObject->validate();

                $results[] = $r;
            }

            return $results;
        }

        /**
         * Returns an array of responses
         *
         * @param \KimchiRPC\Objects\Response[] $responses
         * @return array
         * @inheritDoc
         */
        public function prepareResponse(array $responses): array
        {
            if(count($responses) > 1)
            {
                $results = [];
                foreach($responses as $response)
                {
                    $results[] = Response::fromServerResponse($response)->toArray();
                }
                return $results;
            }
            else
            {
                return Response::fromServerResponse($responses[0])->toArray();
            }
        }

        /**
         * Handles the HTTP response and sets the appropriate headers
         *
         * @param \KimchiRPC\Objects\Response[] $responses
         */
        public function handleResponse(array $responses)
        {
            http_response_code(200);
            header("Content-Type: " . ProtocolContentTypes::JsonRPC);
            Helper::setServerHeaders(ProtocolType::JsonRpc2);
            if(count($responses) == 0)
            {
                print("");
            }
            else
            {
                print(json_encode($this->prepareResponse($responses), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }
        }

        /**
         * Handles a exception response to the server
         *
         * @param Exception $e
         * @param bool $suppress_non_internal
         */
        public function handleException(Exception $e, bool $suppress_non_internal=false)
        {
            // TODO: Make sure exceptions don't return sensitive information (Exception handling)
            header("Content-Type: " . ProtocolContentTypes::JsonRPC);
            Helper::setServerHeaders(ProtocolType::JsonRpc2);

            $response = new \KimchiRPC\Objects\Response();
            $response->Protocol = ProtocolType::JsonRpc2;
            $response->Success = false;

            if($suppress_non_internal == false)
            {
                $response->ErrorMessage = $e->getMessage();
                $response->ErrorCode = $e->getCode();
            }
            else
            {
                if(in_array($e->getCode(), ServerErrorCodes::AllCodes) || in_array($e->getCode(), JsonStandardErrorCodes::AllCodes))
                {
                    $response->ErrorMessage = $e->getMessage();
                    $response->ErrorCode = $e->getCode();
                }
                else
                {
                    $response->ErrorMessage = "There was an internal server error while trying to handle your request";
                    $response->ErrorCode = ServerErrorCodes::InternalError;
                }
            }

            print(json_encode(Response::fromServerResponse($response)->toArray()));
        }

        /**
         * Parses the HTTP request
         *
         * @param string $request_method
         * @param array $options
         * @return array|Request[]
         * @throws BadRequestException
         * @throws InvalidRequestException
         * @throws MalformedRequestException
         * @throws UnsupportedHttpRequestMethodException
         */
        public function fromRequest(string $request_method, array $options = []): array
        {
            switch(strtoupper($request_method))
            {
                case RequestMethod::POST:
                    $body = file_get_contents('php://input');
                    $json_structure = json_decode($body, true);

                    if($json_structure == false)
                        throw new MalformedRequestException("Cannot decode the JSON data in the request body");

                    return $this->getQueries($json_structure);

                case RequestMethod::GET:
                    if(isset($_GET["method"]) == false)
                        throw new BadRequestException("Missing 'method' parameter");

                    $parameters = [];
                    if(isset($_GET["parameters"]))
                    {
                        $parameters = json_decode($_GET["parameters"], true);
                        if($parameters == false)
                        {
                            $parameters = json_decode(base64_decode($_GET["parameters"]));
                            if($parameters == false)
                            {
                                throw new BadRequestException("Cannot parse 'params' parameter");
                            }
                        }
                    }

                    $request_object = new Request();
                    $client_ip = Helper::getClientIP();
                    $request_object->Method = $_GET["method"];
                    $request_object->ID = ($_GET["id"] ?? null);
                    $request_object->Parameters = $parameters;

                    return [\KimchiRPC\Objects\Request::fromJsonRpcRequest($request_object, $client_ip)];

                default:
                    throw new UnsupportedHttpRequestMethodException("The request method '" . $request_method . "' is not supported");
            }
        }
    }