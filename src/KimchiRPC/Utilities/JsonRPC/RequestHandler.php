<?php


    namespace KimchiRPC\Utilities\JsonRPC;

    use KimchiRPC\Exceptions\JsonRPC\InvalidRequestException;
    use KimchiRPC\Exceptions\Server\BadRequestException;
    use KimchiRPC\Exceptions\Server\MalformedRequestException;
    use KimchiRPC\Objects\JsonRPC\Request;
    use KimchiRPC\Objects\JsonRPC\Response;
    use ppm\Classes\DirectoryScanner\Exception;

    /**
     * Class RequestHandler
     * @package KimchiRPC\Utilities\JsonRPC
     */
    class RequestHandler
    {
        /**
         * Attempts to determine if the request is a batch request
         *
         * @param array $data
         * @return bool
         */
        public static function isBatchRequest(array $data): bool
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
         * Attempts to get the batch query
         *
         * @param array $data
         * @param bool $validate
         * @return array|Request
         * @throws InvalidRequestException
         */
        public static function getQueries(array $data, bool $validate=true): array
        {
            /** @var Request $results */
            $results = [];

            if(self::isBatchRequest($data))
            {
                foreach($data as $datum)
                {
                    $results[] = Request::fromArray($datum);
                }

                if($validate)
                {
                    foreach($results as $request)
                        $request->validate();
                }

            }
            else
            {
                $r = Request::fromArray($data);
                if($validate)
                    $r->validate();

                $results[] = $r;
            }

            return $results;
        }

        /**
         * Prepares the responses, if multiple responses then it will return an array of responses
         * if one response then it will return a single response in an array representation
         *
         * @param \KimchiRPC\Objects\Response[] $responses
         * @return array
         */
        public static function prepareResponse(array $responses): array
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
         * Parses the request batch from a POST Body
         *
         * @return array
         * @throws InvalidRequestException
         * @throws MalformedRequestException
         */
        public static function fromPostRequest(): array
        {
            $body = file_get_contents('php://input');
            $json_structure = json_decode($body, true);

            if($json_structure == false)
                throw new MalformedRequestException("Cannot decode the JSON data in the request body");

            return self::getQueries($json_structure);
        }

        /**
         * @return Request[]
         * @throws BadRequestException
         */
        public static function fromGetRequest(): array
        {
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

            return [$request_object];
        }
    }