<?php

namespace App\Helpers;

class DecodeJson
{
    public static function decodeJsonRecursive($data)
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data = $decoded;
            }
        }

        if (is_array($data)) {
            foreach ($data as &$value) {
                $value = self::decodeJsonRecursive($value);
            }
        }

        return $data;
    }
}
