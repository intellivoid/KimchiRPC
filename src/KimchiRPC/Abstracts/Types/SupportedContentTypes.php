<?php


    namespace KimchiRPC\Abstracts\Types;

    /**
     * Class SupportedContentTypes
     * @package KimchiRPC\Abstracts\Types
     */
    abstract class SupportedContentTypes
    {
        const JsonRpc = "application/json-rpc";
        const JsonRpc2 = "application/json";
        const JsonRpc3 = "application/jsonrequest";
    }