<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiRPC\Objects\JsonRPC;

    use KimchiRPC\Exceptions\JsonRPC\InvalidRequestException;

    /**
     * When a rpc call is made, the Server MUST reply with a Response, except for in the case of Notifications.
     * The Response is expressed as a single JSON Object
     *
     * Class Request
     * @package KimchiRPC\Objects\JsonRPC
     */
    class Request
    {
        /**
         * A String specifying the version of the JSON-RPC protocol. MUST be exactly "2.0".
         *
         * @var string
         */
        public $JsonRPC;

        /**
         * A String containing the name of the method to be invoked. Method names that begin with the word rpc followed
         * by a period character (U+002E or ASCII 46) are reserved for rpc-internal methods and extensions and MUST NOT
         * be used for anything else..
         *
         * @var string
         */
        public $Method;

        /**
         * A Structured value that holds the parameter values to be used during the invocation of the method.
         * This member MAY be omitted.
         *
         * @var array|null
         */
        public $Parameters;

        /**
         * An identifier established by the Client that MUST contain a String, Number, or NULL value if included.
         * If it is not included it is assumed to be a notification. The value SHOULD normally not be Null
         * and Numbers SHOULD NOT contain fractional parts
         *
         * @var string|int|null
         */
        public $ID;

        /**
         * Validates the request object values to abid by the standard, if something is out of place then a
         * InvalidRequestException (standard) will be raised with the details of the error.
         *
         * @throws InvalidRequestException
         */
        public function validate()
        {
            if($this->JsonRPC !== "2.0")
                throw new InvalidRequestException(
                    "The property 'jsonrpc' must be '2.0', got an unsupported version");

            if($this->Method == null || gettype($this->Method) !== "string")
                throw new InvalidRequestException(
                    "The property 'method' must be string, got " . strtolower(gettype($this->Method)));

            switch(strtolower(gettype($this->ID)))
            {
                case "string":
                    if(is_numeric($this->ID) == false)
                        throw new InvalidRequestException(
                            "The property 'id' must be a integer or null. got " . strtolower(gettype($this->ID)));

                    // Cast it as int32
                    $this->ID = (int)$this->ID;
                    break;

                case "integer":
                case "null":
                    break;

                default:
                    throw new InvalidRequestException(
                        "The property 'id' must be a integer or null. got " . strtolower(gettype($this->ID)));
            }

            switch(strtolower(gettype($this->Parameters)))
            {
                case "array":
                case "null":
                    break;

                default:
                    throw new InvalidRequestException(
                        "The property 'params' can be array or null, got " . strtolower(gettype($this->ID)));
            }
        }

        /**
         * Makes a successful response object to this request
         *
         * @param $results
         * @return Response
         */
        public function makeSuccessfulResponse($results): Response
        {
            $response_object = new Response();
            $response_object->ID = $this->ID;
            $response_object->Result = $results;
            return $response_object;
        }

        /**
         * Makes a error response object to this request
         *
         * @param Error $error
         * @return Response
         */
        public function makeErrorResponse(Error $error): Response
        {
            $response_object = new Response();
            $response_object->ID = $this->ID;
            $response_object->Error = $error;
            return $response_object;
        }

        /**
         * Returns an array representation of the object
         *
         * @param bool $include_id If the ID isn't included, it will be considered a notification request
         * @return array
         */
        public function toArray(bool $include_id=true): array
        {
            $results = [
                "jsonrpc" => $this->JsonRPC,
                "method" => $this->Method
            ];

            if($this->Parameters !== null)
                $results["params"] = $this->Parameters;

            if($include_id)
                $results["id"] = $this->ID;

            return $results;
        }

        /**
         * Constructs object from array
         *
         * @param array $data
         * @return Request
         */
        public static function fromArray(array $data): Request
        {
            $request_object = new Request();

            if(isset($data["jsonrpc"]))
                $request_object->JsonRPC = $data["jsonrpc"];

            if(isset($data["method"]))
                $request_object->Method = $data["method"];

            if(isset($data["params"]))
                $request_object->Parameters = $data["params"];

            if(isset($data["id"]))
                $request_object->ID = $data["id"];

            return $request_object;
        }
    }