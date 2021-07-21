<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiRPC\Objects;

    use Exception;
    use KimchiRPC\Abstracts\Types\ProtocolType;

    /**
     * Class Response
     * @package KimchiRPC\Objects
     */
    class Response
    {
        /**
         * The ID of the response in co-relation to the request
         *
         * @var int
         */
        public $ID;

        /**
         * The protocol that this response is going to use
         *
         * @var string|ProtocolType
         */
        public $Protocol;

        /**
         * Indicates if the response was successful or not
         *
         * @var bool
         */
        public $Success;

        /**
         * The code of the error if an error is to be raised
         *
         * @var int|null
         */
        public $ErrorCode;

        /**
         * The message of the error if an error is to be raised
         *
         * @var string|null
         */
        public $ErrorMessage;

        /**
         * Optional information about the error in a data form
         *
         * @var array|string|int|bool|null
         */
        public $ErrorData;

        /**
         * The result data of the request
         *
         * @var array|string|int|bool|null
         */
        public $ResultData;

        /**
         * Returns an array representation of the object
         *
         * @param bool $compact Returns a compact representation for ZiProto serialization
         * @return array
         */
        public function toArray(bool $compact=false): array
        {
            if($compact)
            {
                return [
                    0x001 => $this->ID,
                    0x002 => $this->Protocol,
                    0x003 => $this->Success,
                    0x004 => $this->ErrorCode,
                    0x005 => $this->ErrorMessage,
                    0x006 => $this->ErrorData,
                    0x007 => $this->ResultData
                ];
            }
            else
            {
                return [
                    "id" => $this->ID,
                    "protocol" => $this->Protocol,
                    "success" => $this->Success,
                    "error_code" => $this->ErrorCode,
                    "error_message" => $this->ErrorMessage,
                    "error_data" => $this->ErrorData,
                    "result_data" => $this->ResultData
                ];
            }
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return Response
         */
        public static function fromArray(array $data): Response
        {
            $response_object = new Response();

            if(isset($data[0x001]))
                $response_object->ID = $data[0x001];
            if(isset($data["id"]))
                $response_object->ID = $data["id"];

            if(isset($data[0x002]))
                $response_object->Protocol = $data[0x002];
            if(isset($data["protocol"]))
                $response_object->Protocol = $data[0x002];

            if(isset($data[0x003]))
                $response_object->Success = $data[0x003];
            if(isset($data["success"]))
                $response_object->Success = $data["success"];

            if(isset($data[0x004]))
                $response_object->ErrorCode = $data[0x004];
            if(isset($data["error_code"]))
                $response_object->ErrorCode = $data["error_code"];

            if(isset($data[0x005]))
                $response_object->ErrorMessage = $data[0x005];
            if(isset($data["error_message"]))
                $response_object->ErrorMessage = $data["error_message"];

            if(isset($data[0x006]))
                $response_object->ErrorData = $data[0x006];
            if(isset($data["error_data"]))
                $response_object->ErrorData = $data["error_data"];

            if(isset($data[0x007]))
                $response_object->ResultData = $data[0x007];
            if(isset($data["result_data"]))
                $response_object->ResultData = $data["result_data"];

            return $response_object;
        }

        /**
         * Constructs a response from an exception
         *
         * @param ProtocolType|string $protocol_type
         * @param int|null $id
         * @param Exception $exception
         * @return Response
         */
        public static function fromException(string $protocol_type, ?int $id, Exception $exception): Response
        {
            $response_object = new Response();

            $response_object->Success = false;
            $response_object->ErrorCode = $exception->getCode();
            $response_object->ErrorMessage = $exception->getMessage();
            $response_object->ID = $id;
            $response_object->Protocol = $protocol_type;

            return $response_object;
        }

        /**
         * Constructs a basis response based off the request
         *
         * @param Request $request
         * @return Response
         */
        public static function fromRequest(Request $request): Response
        {
            $response_object = new Response();

            $response_object->Success = true;
            $response_object->ID = $request->ID;
            $response_object->Protocol = $request->ProtocolType;

            return $response_object;
        }
    }