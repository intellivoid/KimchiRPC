<?php


    namespace KimchiRPC\Classes\RequestHandlers;


    use KimchiRPC\Abstracts\Types\RequestMethod;
    use KimchiRPC\Exceptions\JsonRPC\InvalidRequestException;
    use KimchiRPC\Exceptions\Server\BadRequestException;
    use KimchiRPC\Exceptions\Server\MalformedRequestException;
    use KimchiRPC\Exceptions\Server\UnsupportedHttpRequestMethodException;
    use KimchiRPC\Interfaces\RequestHandlerInterface;
    use KimchiRPC\Objects\JsonRPC\Request;
    use KimchiRPC\Objects\JsonRPC\Response;

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

            if($this->isBatchRequest($data))
            {
                foreach($data as $datum)
                {
                    $results[] = \KimchiRPC\Objects\Request::fromJsonRpcRequest(Request::fromArray($datum));
                }

                if($validate)
                {
                    foreach($results as $request)
                        $request->ProtocolRequestObject->validate();
                }

            }
            else
            {
                $r = \KimchiRPC\Objects\Request::fromJsonRpcRequest(Request::fromArray($data));
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

                    if(isset($_GET["id"]) == false)
                        throw new BadRequestException("Missing 'id' parameter");

                    $parameters = [];
                    if(isset($_GET["parameters"]) == false)
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
                    $request_object->Method = $_GET["method"];
                    $request_object->ID = $_GET["id"];
                    $request_object->Parameters = $parameters;

                    return [\KimchiRPC\Objects\Request::fromJsonRpcRequest($request_object)];

                default:
                    throw new UnsupportedHttpRequestMethodException("The request method '" . $request_method . "' is not supported");
            }
        }
    }