<?php


    namespace KimchiRPC\Utilities;

    use ZiProto\ZiProto;

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
            $out = preg_replace("/(?>[^\w\.])+/", "_", $input);
            // Replace any underscores at start or end of the string.
            if ($out[0] == "_")
            {
                $out = substr($out, 1);
            }
            if ($out[-1] == "_")
            {
                $out = substr($out, 0, -1);
            }

            return $out;
        }

        /**
         * Truncates a long string
         *
         * @param string $input
         * @param int $length
         * @return string
         */
        public static function truncateString(string $input, int $length): string
        {
            return (strlen($input) > $length) ? substr($input,0, $length).'...' : $input;
        }

        /**
         * Encodes data using ZiProto
         *
         * @param array $data
         * @return string
         */
        public static function encode(array $data): string
        {
            return base64_encode(ZiProto::encode($data));
        }

        /**
         * Returns decoded data using ZiProto
         *
         * @param string $data
         * @return array
         */
        public static function decode(string $data): array
        {
            return ZiProto::decode(base64_decode($data));
        }
    }