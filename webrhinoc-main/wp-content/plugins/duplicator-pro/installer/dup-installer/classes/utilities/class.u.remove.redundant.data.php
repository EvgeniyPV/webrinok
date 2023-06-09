<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Params\PrmMng;

class DUPX_RemoveRedundantData
{

    public static function loadWP()
    {
        static $loaded = null;
        if (is_null($loaded)) {
            $wp_root_dir = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW);
            require_once($wp_root_dir . '/wp-load.php');
            if (!class_exists('WP_Privacy_Policy_Content')) {
                require_once($wp_root_dir . '/wp-admin/includes/misc.php');
            }
            if (!function_exists('request_filesystem_credentials')) {
                require_once($wp_root_dir . '/wp-admin/includes/file.php');
            }
            if (!function_exists('get_plugins')) {
                require_once $wp_root_dir . '/wp-admin/includes/plugin.php';
            }
            if (!function_exists('delete_theme')) {
                require_once $wp_root_dir . '/wp-admin/includes/theme.php';
            }
            $GLOBALS['wpdb']->show_errors(false);
            $loaded = true;
        }
        return $loaded;
    }

    /**
     *
     * @param stdClass $theme
     * @return boolean
     */
    protected static function isThemeEnable($theme)
    {
        switch (DUPX_InstallerState::getInstType()) {
            case DUPX_InstallerState::INSTALL_SINGLE_SITE:
            case DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE:
            case DUPX_InstallerState::INSTALL_RECOVERY_SINGLE_SITE:
                if ($theme->isActive) {
                    return true;
                }
                break;
            case DUPX_InstallerState::INSTALL_MULTISITE_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_MULTISITE_SUBFOLDER:
            case DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBFOLDER:
            case DUPX_InstallerState::INSTALL_RECOVERY_MULTISITE_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_RECOVERY_MULTISITE_SUBFOLDER:
                if (count($theme->isActive) > 0) {
                    return true;
                }
                break;
            case DUPX_InstallerState::INSTALL_STANDALONE:
                if (in_array(PrmMng::getInstance()->getValue(PrmMng::PARAM_SUBSITE_ID), $theme->isActive)) {
                    return true;
                }
                break;
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
            case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBFOLDER:
                return true;
            case DUPX_InstallerState::INSTALL_NOT_SET:
            default:
                throw new Exception('Invalid installer type');
        }

        return false;
    }

    /**
     *
     * @param stdClass $parentTheme
     * @param stdClass[] $themes
     * @return boolean
     */
    protected static function haveChildEnable($parentTheme, &$themes)
    {
        foreach ($themes as $theme) {
            if ($theme->parentTheme === $parentTheme->slug) {
                if (self::isThemeEnable($theme)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function deleteRedundantThemes()
    {
        Log::info("\n--------------------\n" .
            "DELETING INACTIVE THEMES");

        self::loadWP();

        $themes = DUPX_ArchiveConfig::getInstance()->wpInfo->themes;

        foreach ($themes as $theme) {
            //Log::info('THEME: '.Log::v2str($theme));

            if (self::isThemeEnable($theme)) {
                Log::info('THEME: ' . Log::v2str($theme->slug) . ' ENABLE');
                continue;
            }
            if (self::haveChildEnable($theme, $themes)) {
                Log::info('THEME: ' . Log::v2str($theme->slug) . ' CHILD ENABLE');
                continue;
            }
            if (delete_theme($theme->stylesheet, '')) {
                Log::info('THEME: ' . Log::v2str($theme->slug) . ' DELETED');
            } else {
                $nManager = DUPX_NOTICE_MANAGER::getInstance();
                $errorMsg = "**ERROR** The Inactive theme " . $theme->slug . " deletion failed";
                Log::info($errorMsg);

                $fullPath = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_CONTENT_NEW) . '/themes/' . $theme->stylesheet;

                $nManager->addFinalReportNotice(array(
                    'shortMsg' => $errorMsg,
                    'level'    => DUPX_NOTICE_ITEM::HARD_WARNING,
                    'longMsg'  => 'Please delete the path ' . $fullPath . ' manually',
                    'sections' => 'general'
                ));
            }
        }
    }
}
