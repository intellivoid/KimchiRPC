<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiRPC\Objects\JsonRPC;

    /**
     * Class Response
     * @package KimchiRPC\Objects\JsonRPC
     */
    class Response
    {
        /**
         * A String specifying the version of the JSON-RPC protocol. MUST be exactly "2.0".
         *
         * @var string
         */
        public $JsonRPC;

        /**
         * This member is REQUIRED on success. This member MUST NOT exist if there was an error invoking the method.
         * The value of this member is determined by the method invoked on the Server.
         *
         * @var array|string|int|bool|null
         */
        public $Result;

        /**
         * This member is REQUIRED on error. This member MUST NOT exist if there was no error triggered during invocation.
         * The value for this member MUST be an Object as defined in section 5.1.
         *
         * @var Error|null
         */
        public $Error;

        /**
         * This member is REQUIRED.
         * It MUST be the same as the value of the id member in the Request Object. If there was an error in detecting
         * the id in the Request object (e.g. Parse error/Invalid Request), it MUST be Null.
         *
         * @var string|int|null
         */
        public $ID;

        /**
         * Response constructor.
         */
        public function __construct()
        {
            $this->JsonRPC = "2.0";
        }

        /**
         * Determines if the response is an error or not
         *
         * @return bool
         */
        public function isError(): bool
        {
            if($this->Error !== null)
                return true;

            return false;
        }

        /**
         * Returns an array representation of the response
         *
         * @return string[]
         */
        public function toArray(): array
        {
            $results = [
                "jsonrpc" => $this->JsonRPC,
            ];

            if($this->isError())
            {
                $results["error"] = $this->Error->toArray();
            }
            else
            {
                $results["result"] = $this->Result;
            }

            $results["id"] = $this->ID;

            return $results;
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return Response
         */
        public static function fromArray(array $data): Response
        {
            $response_object = new Response();

            if(isset($data["jsonrpc"]))
                $response_object->JsonRPC = $data["jsonrpc"];

            if(isset($data["result"]))
                $response_object->Result = $data["result"];

            if(isset($data["error"]))
                $response_object->Error = Error::fromArray($data["error"]);

            if(isset($data["id"]))
                $response_object->ID = $data["id"];
        }
    }