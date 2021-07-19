<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiRPC\Objects\JsonRPC;

    use Exception;
    use KimchiRPC\Abstracts\ErrorCodes\JsonStandardErrorCodes;
    use KimchiRPC\Exceptions\JsonRPC\InternalErrorException;

    /**
     * When a rpc call encounters an error, the Response Object MUST contain the error member with a value that is a
     * Object
     *
     * Class Error
     * @package KimchiRPC\Objects\JsonRPC
     */
    class Error
    {
        /**
         * A Number that indicates the error type that occurred.
         *
         * @var int|JsonStandardErrorCodes
         */
        public $Code;

        /**
         * A String providing a short description of the error. The message SHOULD be limited to a concise single sentence.
         *
         * @var string
         */
        public $Message;

        /**
         * A Primitive or Structured value that contains additional information about the error. This may be omitted.
         * The value of this member is defined by the Server (e.g. detailed error information, nested errors etc.).
         *
         * @var string|array|null
         */
        public $Data;

        /**
         * Validates the error object values to abid by the standard, if something is out of place then a
         * ServerException (standard) will be raised with the details of the error.
         *
         * @throws InternalErrorException
         */
        public function validate()
        {
            if(strtolower(gettype($this->Code)) !== "integer")
                throw new InternalErrorException("The property 'code' must be integer, got" . strtolower(gettype($this->Code)));

            switch(strtolower(gettype($this->Data)))
            {
                case "integer":
                case "string":
                case "array":
                case "null":
                    break;

                default:
                    throw new InternalErrorException("The property 'error' can be integer, string, array or null. got" . strtolower(gettype($this->Data)));
            }

            if(strtolower(gettype($this->Message)) !== "string")
                throw new InternalErrorException("The property 'message' must be string, got" . strtolower(gettype($this->Message)));
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            $results = [
                "code" => $this->Code,
                "message" => $this->Message
            ];

            if($this->Data !== null)
                $results["data"] = $this->Data;

            return $results;
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return Error
         */
        public static function fromArray(array $data): Error
        {
            $error_object = new Error();

            if(isset($data["code"]))
                $error_object->Code = $data["code"];

            if(isset($data["message"]))
                $error_object->Message = $data["message"];

            if(isset($data["data"]))
                $error_object->Data = $data["error"];

            return $error_object;
        }

        /**
         * Constructs error from an exception
         *
         * @param Exception $e
         * @return Error
         */
        public static function fromException(Exception $e): Error
        {
            $error_object = new Error();

            $error_object->Code = $e->getCode();
            $error_object->Message = $e->getMessage();

            return $error_object;
        }
    }