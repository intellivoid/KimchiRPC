<?php


    namespace KimchiRPC\Utilities;

    /**
     * Class Helper
     * @package KimchiRPC\Utilities
     */
    class Helper
    {
        /**
         * Returns an array of headers from the HTTP request
         *
         * @return array
         */
        public static function getRequestHeaders(): array
        {
            if(function_exists('getallheaders'))
                return getallheaders();

            $results = [];

            foreach($_SERVER as $key=>$value)
            {
                if (substr($key,0,5) == "HTTP_")
                {
                    $key= str_replace(" ","-", ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
                    $results[$key]=$value;

                }
                else
                {
                    $results[$key]=$value;
                }
            }

            return $results;
        }
    }