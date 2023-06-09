<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapJson;

class DUP_PRO_JSON_U
{
    protected static $_messages = array(
        JSON_ERROR_NONE           => 'No error has occurred',
        JSON_ERROR_DEPTH          => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR      => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX         => 'Syntax error',
        JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded. To resolve see https://snapcreek.com/duplicator/docs/faqs-tech/#faq-package-170-q'
    );

    public static function customEncode($value, $iteration = 1)
    {
        $encoded = SnapJson::jsonEncodePPrint($value);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                DUP_PRO_LOG::trace("#### no json errors so returning");
                return $encoded;
            case JSON_ERROR_DEPTH:
                throw new RuntimeException('Maximum stack depth exceeded'); // or trigger_error() or throw new Exception()
            case JSON_ERROR_STATE_MISMATCH:
                throw new RuntimeException('Underflow or the modes mismatch'); // or trigger_error() or throw new Exception()
            case JSON_ERROR_CTRL_CHAR:
                throw new RuntimeException('Unexpected control character found');
            case JSON_ERROR_SYNTAX:
                throw new RuntimeException('Syntax error, malformed JSON'); // or trigger_error() or throw new Exception()
            case JSON_ERROR_UTF8:
                if ($iteration == 1) {
                    DUP_PRO_LOG::trace("#### utf8 error so redoing");
                    $clean = self::makeUTF8($value);
                    return self::customEncode($clean, $iteration + 1);
                } else {
                    throw new RuntimeException('UTF-8 error loop');
                }
            default:
                throw new RuntimeException('Unknown error'); // or trigger_error() or throw new Exception()
        }
    }

    public static function safeEncode($data, $options = 0, $depth = 512)
    {
        try {
            $jsonString = SnapJson::jsonEncode($data, $options, $depth);
        } catch (Exception $e) {
            $jsonString = false;
        }

        if (($jsonString === false) || trim($jsonString) == '') {
            $jsonString = self::customEncode($value);

            if (($jsonString === false) || trim($jsonString) == '') {
                throw new Exception('Unable to generate JSON from object');
            }
        }
        return $jsonString;
    }

    public static function decode($json, $assoc = false)
    {
        return json_decode($json, $assoc);
    }

    /** ========================================================
     * PRIVATE METHODS
     * =====================================================  */
    private static function makeUTF8($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = self::makeUTF8($value);
            }
        } else if (is_string($mixed)) {
            return utf8_encode($mixed);
        }
        return $mixed;
    }

    private static function escapeString($str)
    {
        return addcslashes($str, "\v\t\n\r\f\"\\/");
    }

    private static function oldMakeUTF8($val)
    {
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                $val[$k] = self::oldMakeUTF8($v);
            }
        } else if (is_object($val)) {
            foreach ($val as $k => $v) {
                $val->$k = self::oldMakeUTF8($v);
            }
        } else {
            if (mb_detect_encoding($val, 'UTF-8', true)) {
                return $val;
            } else {
                return utf8_encode($val);
            }
        }

        return $val;
    }
}
