<?php

/**
 * License class
 *
 * @category  Duplicator
 * @package   Installer
 * @author    Snapcreek <admin@snapcreek.com>
 * @copyright 2011-2021  Snapcreek LLC
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 */

namespace Duplicator\Installer\Addons\ProBase;

use Duplicator\Installer\Core\Params\PrmMng;

class License
{
    const TYPE_UNLICENSED    = 0;
    const TYPE_PERSONAL      = 1;
    const TYPE_FREELANCER    = 2;
    const TYPE_BUSINESS_GOLD = 3;

    /**
     * Returns the license type this installer file is made of.
     *
     * @return obj  Returns an enum type of License
     */
    public static function getType()
    {
        return max(self::getImporterLicense(), self::getInstallerLicense());
    }

    /**
     * Get license on installer from package data
     *
     * @return obj  Returns an enum type of License
     */
    public static function getInstallerLicense()
    {
        return self::getTypeFromLimit(\DUPX_ArchiveConfig::getInstance()->license_limit);
    }

    /**
     * Get importer license from params data
     *
     * @return obj  Returns an enum type of License
     */
    public static function getImporterLicense()
    {
        $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
        return isset($overwriteData['dupLicense']) ? $overwriteData['dupLicense'] : self::TYPE_UNLICENSED;
    }

    /**
     * Returns an enum type of License
     *
     * @param int $limit // sites limit
     *
     * @return obj
     */
    protected static function getTypeFromLimit($limit)
    {
        if ($limit < 0) {
            return self::TYPE_UNLICENSED;
        } elseif ($limit < 15) {
            return self::TYPE_PERSONAL;
        } elseif ($limit < 500) {
            return self::TYPE_FREELANCER;
        } else {
            return self::TYPE_BUSINESS_GOLD;
        }
    }

    /**
     * Return true if multisite plug funcs are enalbed
     *
     * @return bool
     */
    public static function multisitePlusEnabled()
    {
        return self::getType() == self::TYPE_BUSINESS_GOLD;
    }

    /**
     * Return license description
     *
     * @param int  $license license type, if null get current license type
     * @param bool $article if true add article before description
     *
     * @return string
     */
    public static function getLicenseToString($license = null, $article = false)
    {
        if (is_null($license)) {
            $license = self::getType();
        }

        switch ($license) {
            case self::TYPE_BUSINESS_GOLD:
                return ($article ? 'a ' : '') . 'Business or Gold';
            case self::TYPE_UNLICENSED:
                return ($article ? 'an ' : '') . 'unlicensed';
            case self::TYPE_PERSONAL:
                return ($article ? 'a ' : '') . 'Personal';
            case self::TYPE_FREELANCER:
                return ($article ? 'a ' : '') . 'Freelancer';
            default:
                return ($article ? 'an ' : '') . 'unknown license type';
        }
    }


    /**
     * Return license required note
     *
     * @param int $required license required
     *
     * @return string
     */
    public static function getLicenseNote($required)
    {
        if (self::getType() >= $required) {
            return '';
        }

        return 'Requires <b>' . self::getLicenseToString($required) .
            '</b> license. The effective license of this install is ' .
            self::getLicenseToString(null, false) . '.';
    }
}
