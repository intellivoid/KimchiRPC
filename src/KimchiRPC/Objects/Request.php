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
         * The Protocol used to make this request
         *
         * @var string|ProtocolType
         */
        public $ProtocolType;

        /**
         * The Protocol's request object for this request, reference purposes.
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
                    /** @var \KimchiRPC\Objects\JsonRPC\Request $protocol_object */
                    $protocol_object->toArray();
                    break;

                default:
                    throw new ServerException("Cannot serialize '" . $this->ProtocolType . "' protocol object");
            }

            if($compact)
            {
                return [
                    0x001 => $this->ProtocolType,
                    0x002 => $protocol_object,
                    0x003 => $this->Method,
                    0x004 => $this->Parameters
                ];
            }
            else
            {
                return [
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

            if(isset($data["protocol_type"]))
                $request_object->ProtocolType = $data["protocol_type"];
            if(isset($data[0x001]))
                $request_object->ProtocolType = $data[0x001];

            if(isset($data["method"]))
                $request_object->Method = $data["method"];
            if(isset($data[0x003]))
                $request_object->Method = $data[0x003];

            if(isset($data["parameters"]))
                $request_object->Parameters = $data["parameters"];
            if(isset($data[0x004]))
                $request_object->Parameters = $data[0x004];

            $protocol_object = null;
            if(isset($data["protocol_object"]))
                $protocol_object = $data["protocol_object"];
            if(isset($data[0x002]))
                $protocol_object = $data[0x002];

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

            return $request_object;
        }
    }