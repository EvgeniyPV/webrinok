<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Libs\Snap;

class SnapUtil
{
    /** @var int used in custom filter_input_custom for request (check _POST and _GET */
    const INPUT_REQUEST = 10000;

    /**
     * Return array element value or default if not set
     *
     * @param array   $array    input array
     * @param string  $key      array key
     * @param boolean $required if is required thorw Exception if isn't in array
     * @param mixed   $default  default value
     *
     * @return mixed
     */
    public static function getArrayValue($array, $key, $required = true, $default = null)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        } else {
            if ($required) {
                throw new \Exception("Key {$key} not present in array");
            } else {
                return $default;
            }
        }
    }

    /**
     * Gets the calling function name from where this method is called
     *
     * @param integer $backTraceBack backtrace level
     *
     * @return string Returns the calling function name from where this method is called
     */
    public static function getCallingFunctionName($backTraceBack = 0)
    {
        $callers     = debug_backtrace();
        $backTraceL1 = 1 + $backTraceBack;
        $backTraceL2 = 2 + $backTraceBack;
        $result      = '[' . str_pad(basename($callers[$backTraceL1]['file']), 25, '_', STR_PAD_RIGHT) . ':'
            . str_pad($callers[$backTraceL1]['line'], 4, ' ', STR_PAD_LEFT) . ']';
        if (isset($callers[$backTraceL2]) && (isset($callers[$backTraceL2]['class']) || isset($callers[$backTraceL2]['function']))) {
            $result .= ' [';
            $result .= isset($callers[$backTraceL2]['class']) ? $callers[$backTraceL2]['class'] . '::' : '';
            $result .= isset($callers[$backTraceL2]['function']) ? $callers[$backTraceL2]['function'] : '';
            $result .= ']';
        }

        return str_pad($result, 80, '_', STR_PAD_RIGHT);
    }

    /**
     * Return a percentage
     *
     * @param int $startingPercent  Low Percentage Limit
     * @param int $endingPercent    High Percentage Limit
     * @param int $totalTaskCount   Total count
     * @param int $currentTaskCount Current count
     *
     * @return int
     */
    public static function getWorkPercent($startingPercent, $endingPercent, $totalTaskCount, $currentTaskCount)
    {
        if ($totalTaskCount > 0) {
            $percent = $startingPercent + (($endingPercent - $startingPercent) * ($currentTaskCount / (float) $totalTaskCount));
        } else {
            $percent = $startingPercent;
        }

        return min(max($startingPercent, $percent), $endingPercent);
    }

    /**
     * Compare two versions like version_compare but the ability to enter the number of levels to compare.
     * For example, if the level is 2 between 4.1.1 and 4.1.2.1, 4.1 is compared with 4.1 and so they are equal.
     *
     * @param string $version1 version one
     * @param string $version2 version two
     * @param string $operator operetor type
     * @param int    $vLevel   version level 0 is all levels
     *
     * @return int|bool
     */
    public static function versionCompare($version1, $version2, $operator = null, $vLevel = 0)
    {
        if ($vLevel > 0) {
            $tV1 = array_slice(explode('.', $version1), 0, $vLevel);
            $version1 = implode('.', $tV1);
            $tV2 = array_slice(explode('.', $version2), 0, $vLevel);
            $version2 = implode('.', $tV2);
        }
        return version_compare($version1, $version2, $operator);
    }

    /**
     * Return true if is PHP7+
     *
     * @return bool
     */
    public static function isPHP7Plus()
    {
        static $isPHP7Plus = null;
        if (is_null($isPHP7Plus)) {
            $isPHP7Plus = version_compare(PHP_VERSION, '7.0.0', '>=');
        }
        return $isPHP7Plus;
    }

    /**
     * Groups an array into arrays by a given key, or set of keys, shared between all array members.
     *
     * Based on {@author Jake Zatecky}'s {@link https://github.com/jakezatecky/array_group_by array_group_by()} function.
     * This variant allows $key to be closures.
     *
     * @param array $array The array to have grouping performed on.
     * @param mixed $key   The key to group or split by. Can be a _string_, an _integer_, a _float_, or a _callable_.
     *                     - If the key is a callback, it must return a valid key from the array.
     *                     - If the key is _NULL_, the iterated element is skipped.
     *                     - string|int callback ( mixed $item )
     *
     * @return array|null Returns a multidimensional array or `null` if `$key` is invalid.
     */
    public static function arrayGroupBy(array $array, $key)
    {
        if (!is_string($key) && !is_int($key) && !is_float($key) && !is_callable($key)) {
            trigger_error('array_group_by(): The key should be a string, an integer, or a callback', E_USER_ERROR);
            return null;
        }
        $func    = (!is_string($key) && is_callable($key) ? $key : null);
        $_key    = $key;
        // Load the new array, splitting by the target key
        $grouped = array();
        foreach ($array as $value) {
            $key = null;
            if (is_callable($func)) {
                $key = call_user_func($func, $value);
            } elseif (is_object($value) && isset($value->{$_key})) {
                $key = $value->{$_key};
            } elseif (isset($value[$_key])) {
                $key = $value[$_key];
            }
            if ($key === null) {
                continue;
            }
            $grouped[$key][] = $value;
        }
        // Recursively build a nested grouping if more parameters are supplied
        // Each grouped array value is grouped according to the next sequential key
        if (func_num_args() > 2) {
            $args = func_get_args();
            foreach ($grouped as $key => $value) {
                $params        = array_merge(array($value), array_slice($args, 2, func_num_args()));
                $grouped[$key] = call_user_func_array(array(__CLASS__, 'arrayGroupBy'), $params);
            }
        }
        return $grouped;
    }

    /**
     * Converts human readable types (10GB) to bytes
     *
     * @param string $from A human readable byte size such as 100MB
     *
     * @return int Returns and integer of the byte size
     */
    public static function convertToBytes($from)
    {
        if (is_numeric($from)) {
            return $from;
        }

        $number = substr($from, 0, -2);
        switch (strtoupper(substr($from, -2))) {
            case "KB":
                return $number * 1024;
            case "MB":
                return $number * pow(1024, 2);
            case "GB":
                return $number * pow(1024, 3);
            case "TB":
                return $number * pow(1024, 4);
            case "PB":
                return $number * pow(1024, 5);
        }

        $number = substr($from, 0, -1);
        switch (strtoupper(substr($from, -1))) {
            case "K":
                return $number * 1024;
            case "M":
                return $number * pow(1024, 2);
            case "G":
                return $number * pow(1024, 3);
            case "T":
                return $number * pow(1024, 4);
            case "P":
                return $number * pow(1024, 5);
        }
        return $from;
    }

    /**
     *  Sanitize input for XSS code
     *
     *  @param string $input The value to sanitize
     *
     *  @return string Returns the input value cleaned up.
     */
    public static function sanitize($input)
    {
        return htmlspecialchars(self::sanitizeNSChars($input));
    }

    /**
     * Remove all non stamp chars from string
     *
     * @param string $string input string
     *
     * @return string
     */
    public static function sanitizeNSChars($string)
    {

        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', '', (string) $string);
    }

    /**
     * remove all non stamp chars from string and newline
     * trim string
     *
     * @param string $string input string
     *
     * @return string
     */
    public static function sanitizeNSCharsNewline($string)
    {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F\r\n]/u', '', (string) $string);
    }

    /**
     * Remove all non stamp chars, newline, spaces and tabulation from string
     *
     * @param string $string input string
     *
     * @return string
     */
    public static function sanitizeNSCharsNewlineTabs($string)
    {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F\r\n\s]/u', '', (string) $string);
    }

    /**
     * remove all non stamp chars from string and newline
     * trim string
     *
     * @param string $string input string
     *
     * @return string
     */
    public static function sanitizeNSCharsNewlineTrim($string)
    {
        return trim(self::sanitizeNSCharsNewline($string));
    }

    /**
     * Determines whether a PHP ini value is changeable at runtime.
     *
     * @since 4.6.0
     *
     * @staticvar array $ini_all
     *
     * @link https://secure.php.net/manual/en/function.ini-get-all.php
     *
     * @param string $setting The name of the ini setting to check.
     * @return bool True if the value is changeable at runtime. False otherwise.
     */
    public static function isIniValChangeable($setting)
    {
        // if ini_set is disabled can change the values
        if (!function_exists('ini_set')) {
            return false;
        }

        if (function_exists('isIniValChangeable')) {
            return isIniValChangeable($setting);
        }

        static $ini_all;

        if (!isset($ini_all)) {
            $ini_all = false;
            // Sometimes `ini_get_all()` is disabled via the `disable_functions` option for "security purposes".
            if (function_exists('ini_get_all')) {
                $ini_all = ini_get_all();
            }
        }

        // Bit operator to workaround https://bugs.php.net/bug.php?id=44936 which changes access level to 63 in PHP 5.2.6 - 5.2.17.
        if (isset($ini_all[$setting]['access']) && ( INI_ALL === ( $ini_all[$setting]['access'] & 7 ) || INI_USER === ( $ini_all[$setting]['access'] & 7 ) )) {
            return true;
        }

        // If we were unable to retrieve the details, fail gracefully to assume it's changeable.
        if (!is_array($ini_all)) {
            return true;
        }

        return false;
    }

    /**
     * The val value returns if it is between min and max otherwise it returns min or max
     *
     * @param int $val input value
     * @param int $min min value
     * @param int $max max value
     *
     * @return int
     */
    public static function getIntBetween($val, $min, $max)
    {
        return min((int) $max, max((int) $min, (int) $val));
    }

    /**
     * Gets a specific external variable by name and optionally filters it by request
     *
     * @param string    $variable_name <p>Name of a variable to get.</p>
     * @param int       $filter        <p>The ID of the filter to apply. The Types of filters manual page lists the available filters.</p>
     *                                 <p>If omitted, <b><code>FILTER_DEFAULT</code></b> will be used,
     *                                 which is equivalent to <b><code>FILTER_UNSAFE_RAW</code></b>.
     *                                 This will result in no filtering taking place by default.</p>
     * @param array|int $options       <p>Associative array of options or bitwise disjunction of flags.
     *                                 If filter accepts options, flags can be provided in "flags" field of array.</p>
     *
     * @return mixed <p>Value of the requested variable on success, <b><code>FALSE</code></b>
     *               if the filter fails, or <b><code>NULL</code></b>
     *               if the <code>variable_name</code> variable is not set.
     *               If the flag <b><code>FILTER_NULL_ON_FAILURE</code></b> is used,
     *               it returns <b><code>FALSE</code></b> if the variable is not set and <b><code>NULL</code></b> if the filter fails.</p>
     *
     * @link http://php.net/manual/en/function.filter-input.php
     * @see filter_var(), filter_input_array(), filter_var_array()
     */
    public static function filterInputRequest($variable_name, $filter = FILTER_DEFAULT, $options = 0)
    {
        if (isset($_GET[$variable_name]) && !isset($_POST[$variable_name])) {
            return filter_input(INPUT_GET, $variable_name, $filter, $options);
        }

        return filter_input(INPUT_POST, $variable_name, $filter, $options);
    }

    /**
     * Default filter sanitize string, apply sanitizeNSChars function.
     *
     * @param int    $type     One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV.
     * @param string $var_name Name of a variable to get.
     * @param mixed  $default  default value if dont exists
     *
     * @return string
     */
    public static function filterInputDefaultSanitizeString($type, $var_name, $default = '')
    {
        $filter =  FILTER_UNSAFE_RAW;
        $options = array(
            'options' => array( 'default' => null)
        );
        if ($type == self::INPUT_REQUEST) {
            $result = self::filterInputRequest($var_name, $filter, $options);
        } else {
            $result = filter_input($type, $var_name, $filter, $options);
        }
        if (is_null($result)) {
            return $default;
        }
        return self::sanitizeNSChars($result);
    }

    /**
     * Gets external variables and optionally filters them
     * <p>This function is useful for retrieving many values without repetitively calling <code>filter_input()</code>.</p>
     *
     * @param array|int $definition <p>An array defining the arguments.
     *                              A valid key is a <code>string</code> containing a variable name and a valid value is either a filter type,
     *                              or an <code>array</code> optionally specifying the filter, flags and options.
     *                              If the value is an array, valid keys are <i>filter</i> which specifies the filter type,
     *                              <i>flags</i> which specifies any flags that apply to the filter, and <i>options</i>
     *                              which specifies any options that apply to the filter. See the example below for a better understanding.</p>
     *                              <p>This parameter can be also an integer holding a filter constant.
     *                              Then all values in the input array are filtered by this filter.</p>
     * @param bool      $add_empty  <p>Add missing keys as <b><code>NULL</code></b> to the return value.</p>
     *
     * @return mixed <p>An array containing the values of the requested variables on success.
     *               If the input array designated by <code>type</code> is not populated,
     *               the function returns <b><code>NULL</code></b> if the <b><code>FILTER_NULL_ON_FAILURE</code></b> flag is not given,
     *               or <b><code>FALSE</code></b> otherwise. For other failures, <b><code>FALSE</code></b> is returned.</p>
     *               <p>An array value will be <b><code>FALSE</code></b> if the filter fails, or <b><code>NULL</code></b>
     *               if the variable is not set. Or if the flag <b><code>FILTER_NULL_ON_FAILURE</code></b> is used,
     *               it returns <b><code>FALSE</code></b> if the variable is not set and <b><code>NULL</code></b> if the filter fails.
     *               If the <code>add_empty</code> parameter is <b><code>FALSE</code></b>, no array element will be added for unset variables.</p>
     *
     * @link http://php.net/manual/en/function.filter-input-array.php
     * @see filter_input(), filter_var_array()
     */
    public static function filterInputRequestArray($definition = FILTER_DEFAULT, $add_empty = true)
    {
        if (!is_array($definition) || count($definition) === 0) {
            return array();
        }
        $getKeys  = is_array($_GET) ? array_keys($_GET) : array();
        $postKeys = is_array($_POST) ? array_keys($_POST) : array();
        $keys     = array_keys($definition);

        if (count(array_intersect($keys, $getKeys)) && !count(array_intersect($keys, $postKeys))) {
            $type = INPUT_GET;
        } else {
            $type = INPUT_POST;
        }

        $result = filter_input_array($type, $definition, $add_empty);

        if (!is_array($result)) {
            $result = array();
            foreach ($keys as $key) {
                $result[$key] = null;
            }
        }
        return $result;
    }

    /**
     * Implemented array_key_first
     *
     * @link https://www.php.net/manual/en/function.array-key-first.php
     *
     * @param array $arr array input
     *
     * @return int|string|null
     */
    public static function arrayKeyFirst($arr)
    {
        if (!function_exists('array_key_first')) {
            foreach ($arr as $key => $unused) {
                return $key;
            }
            return null;
        } else {
            return array_key_first($arr);
        }
    }

    /**
     * Get number of bit supported by PHP
     *
     * @return string
     */
    public static function getArchitectureString()
    {
        return (PHP_INT_SIZE * 8) . '-bit';
    }

    /**
     * In array check by callback
     *
     * @param array    $haystack array input
     * @param callable $callback callback function
     *
     * @return null|bool
     */
    public static function inArrayExtended($haystack, $callback)
    {
        if (!is_callable($callback)) {
            return null;
        }

        foreach ($haystack as $value) {
            if (call_user_func($callback, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * This is a binary recursion, so it only works on an ordered array of integers.
     * (If it is not ordered, it does not work)
     *
     * The advantage of using this search instead of normal array search is that the complexity goes from O(n) to O(log n).
     *
     * @param int[] $array array values
     * @param int   $x     element to search
     *
     * @return bool
     */
    public static function binarySearch($array, $x)
    {
        if (count($array) === 0) {
            return false;
        }
        $low = 0;
        $high = count($array) - 1;

        while ($low <= $high) {
            $mid = floor(($low + $high) / 2);

            if ($array[$mid] == $x) {
                return true;
            }
            if ($x < $array[$mid]) {
                $high = $mid - 1;
            } else {
                $low = $mid + 1;
            }
        }
        return false;
    }
}
