<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiRPC\Objects;

    use KimchiRPC\Abstracts\Types\ProtocolType;
    use KimchiRPC\Exceptions\ServerException;

    /**
     * Class Request
     * @package KimchiRPC\Objects
     */
    class Request
    {
        /**
         * Indicates if the request is valid or not, if not valid it won't be sent to the worker.
         *
         * @var bool
         */
        public $IsValidRequest;

        /**
         * Indicates if the request should be executed in the background
         *
         * @var bool
         */
        public $BackgroundRequest;

        /**
         * The IP address of the client making the request
         *
         * @var string|null
         */
        public $ClientIP;

        /**
         * The ID of the request defined by the client
         *
         * @var int
         */
        public $ID;

        /**
         * The Protocol used to make this request
         *
         * @var string|ProtocolType
         */
        public $ProtocolType;

        /**
         * The Protocol's request object represented in the respective object
         *
         * @var null|mixed|\KimchiRPC\Objects\JsonRPC\Request
         */
        public $ProtocolRequestObject;

        /**
         * The method that was being invoked
         *
         * @var string
         */
        public $Method;

        /**
         * An array of parameters, this option may not be available
         *
         * @var array|null
         */
        public $Parameters;

        /**
         * Returns an array representation of the object
         *
         * @param bool $compact Returns a compact representation for ZiProto serialization
         * @return array
         * @throws ServerException
         */
        public function toArray(bool $compact=false): array
        {
            $protocol_object = null;

            switch($this->ProtocolType)
            {
                case ProtocolType::JsonRpc2:
                    $protocol_object = $this->ProtocolRequestObject->toArray();
                    break;

                default:
                    throw new ServerException("Cannot serialize '" . $this->ProtocolType . "' protocol object");
            }

            if($compact)
            {
                return [
                    0x001 => $this->ID,
                    0x002 => $this->ProtocolType,
                    0x003 => $protocol_object,
                    0x004 => $this->Method,
                    0x005 => $this->Parameters,
                    0x006 => $this->ClientIP,
                ];
            }
            else
            {
                return [
                    "client_ip" => $this->ClientIP,
                    "id" => $this->ID,
                    "protocol_type" => $this->ProtocolType,
                    "protocol_object" => $protocol_object,
                    "method" => $this->Method,
                    "parameters" => $this->Parameters
                ];
            }
        }

        /**
         * Constructs object from array
         *
         * @param array $data
         * @return Request
         * @throws ServerException
         */
        public static function fromArray(array $data): Request
        {
            $request_object = new Request();

            if(isset($data["id"]))
                $request_object->ID = $data["id"];
            if(isset($data[0x001]))
                $request_object->ID = $data[0x001];
            
            if(isset($data["protocol_type"]))
                $request_object->ProtocolType = $data["protocol_type"];
            if(isset($data[0x002]))
                $request_object->ProtocolType = $data[0x002];

            if(isset($data["method"]))
                $request_object->Method = $data["method"];
            if(isset($data[0x004]))
                $request_object->Method = $data[0x004];

            if(isset($data["parameters"]))
                $request_object->Parameters = $data["parameters"];
            if(isset($data[0x005]))
                $request_object->Parameters = $data[0x005];

            $protocol_object = null;
            if(isset($data["protocol_object"]))
                $protocol_object = $data["protocol_object"];
            if(isset($data[0x003]))
                $protocol_object = $data[0x003];

            if($protocol_object !== null)
            {
                switch($request_object->ProtocolType)
                {
                    case ProtocolType::JsonRpc2:
                        $request_object->ProtocolRequestObject = \KimchiRPC\Objects\JsonRPC\Request::fromArray($protocol_object);
                        break;

                    default:
                        throw new ServerException("Cannot construct '" . $request_object->ProtocolType . "' protocol object");
                }
            }

            if(isset($data["client_ip"]))
                $request_object->ClientIP = $data["client_ip"];
            if(isset($data[0x006]))
                $request_object->ClientIP = $data[0x006];

            return $request_object;
        }

        /**
         * Constructs standard request from a Json RPC request
         *
         * @param JsonRPC\Request $request
         * @param string|null $ip_address
         * @return Request
         */
        public static function fromJsonRpcRequest(\KimchiRPC\Objects\JsonRPC\Request $request, ?string $ip_address=null): Request
        {
            $request_object = new Request();
            $request_object->ClientIP = $ip_address;
            $request_object->ID = $request->ID;
            $request_object->ProtocolType = ProtocolType::JsonRpc2;
            $request_object->Method = $request->Method;
            $request_object->Parameters = $request->Parameters;
            $request_object->ProtocolRequestObject = $request;

            if($request->ID == null)
                $request_object->BackgroundRequest = true;

            if($request_object->Method == null)
                $request_object->IsValidRequest = false;

            return $request_object;
        }
    }