<?php


    namespace KimchiRPC\Utilities\JsonRPC;

    use KimchiRPC\Exceptions\JsonRPC\InvalidRequestException;
    use KimchiRPC\Objects\JsonRPC\Request;
    use KimchiRPC\Objects\JsonRPC\Response;

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
         * @param Response[] $responses
         * @return array
         */
        public static function prepareResponse(array $responses): array
        {
            if(count($responses) > 1)
            {
                $results = [];
                foreach($responses as $response)
                {
                    $results[] = $response->toArray();
                }
                return $results;
            }
            else
            {
                return $responses[0]->toArray();
            }
        }
    }