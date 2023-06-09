<?php

/**
 * Auloader calsses
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Addons\ProBase\License;

/**
 * TEMP CALSS TO TEST LIT VERSION.
 */
final class License
{
    /**
     * GENERAL SETTINGS
     */
    const EDD_DUPPRO_STORE_URL               = 'https://snapcreek.com';
    const EDD_DUPPRO_ITEM_NAME               = 'Duplicator Pro';
    const LICENSE_CACHE_TIME                 = 1209600; // 14 DAYS IN SECONDS
    const LICENSE_KEY_OPTION_NAME            = 'duplicator_pro_license_key';
    const EDD_API_CACHE_TIME                 = 172800; // 48 hours
    const UNLICENSED_SUPER_NAG_DELAY_IN_DAYS = 30;

    /**
     * LICENSE STATUS
     */
    const STATUS_OUT_OF_LICENSES = -3;
    const STATUS_UNCACHED        = -2;
    const STATUS_UNKNOWN         = -1;
    const STATUS_VALID           = 0;
    const STATUS_INVALID         = 1;
    const STATUS_INACTIVE        = 2;
    const STATUS_DISABLED        = 3;
    const STATUS_SITE_INACTIVE   = 4;
    const STATUS_EXPIRED         = 5;

    /**
     * LICENSE TYPES
     */
    const TYPE_UNLICENSED    = 0;
    const TYPE_PERSONAL      = 1;
    const TYPE_FREELANCER    = 2;
    const TYPE_BUSINESS_GOLD = 3;

    /**
     * ACTIVATION REPONSE
     */
    const ACTIVATION_RESPONSE_OK         = 0;
    const ACTIVATION_RESPONSE_POST_ERROR = -1;
    const ACTIVATION_RESPONSE_INVALID    = -2;

    /**
     * Return type of license
     *
     * @return int
     */
    public static function getType()
    {
        return self::TYPE_UNLICENSED;
    }

    /**
     * Check if license is personal
     *
     * @return boolean
     */
    public static function isPersonal()
    {
        return false;
    }

    /**
     *
     * @return boolean
     */
    public static function isFreelancer()
    {
        return false;
    }

    /**
     *
     * @return boolean
     */
    public static function isBusiness()
    {
        return false;
    }

    /**
     *
     * @return boolean
     */
    public static function isGold()
    {
        return false;
    }

    /**
     * Return license strin by status
     *
     * @param int $licenseStatusString license
     *
     * @return string
     */
    private static function getLicenseStatusFromString($licenseStatusString)
    {
        switch ($licenseStatusString) {
            case 'valid':
                return self::STATUS_VALID;
            case 'invalid':
                return self::STATUS_INVALID;
            case 'expired':
                return self::STATUS_EXPIRED;
            case 'disabled':
                return self::STATUS_DISABLED;
            case 'site_inactive':
                return self::STATUS_SITE_INACTIVE;
            case 'inactive':
                return self::STATUS_INACTIVE;
            default:
                return self::STATUS_UNKNOWN;
        }
    }
}
