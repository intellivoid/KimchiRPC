<?php


    namespace KimchiRPC\Utilities;

    /**
     * Class Converter
     * @package KimchiRPC\Utilities
     */
    class Converter
    {
        /**
         * Converts the string to a function safe name
         *
         * @param string $input
         * @return string
         */
        public static function functionNameSafe(string $input): string
        {
            $input = strtolower($input);
            $input = str_ireplace(" ", "_", $input);
            $input = str_ireplace("-", "_", $input);

            return $input;
        }
    }