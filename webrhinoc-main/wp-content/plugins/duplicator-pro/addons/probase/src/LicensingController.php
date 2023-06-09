<?php

/**
 * Version Pro Base functionalities
 *
 * Name: Duplicator PRO base
 * Version: 1
 * Author: Snap Creek
 * Author URI: http://snapcreek.com
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Addons\ProBase;

use DUP_PRO_U;
use DUP_PRO_Global_Entity;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Addons\ProBase\License\License;
use Duplicator\Controllers\SettingsPageController;

class LicensingController
{
    const L2_SLUG_LICENSING = 'licensing';

    /**
     * License controller init
     *
     * @return void
     */
    public static function init()
    {
        add_filter('duplicator_sub_menu_items_' . ControllersManager::SETTINGS_SUBMENU_SLUG, array(__CLASS__, 'licenseSubMenu'));
        add_filter('duplicator_render_page_content_' . ControllersManager::SETTINGS_SUBMENU_SLUG, array(__CLASS__, 'renderLicenseContent'));
    }

    /**
     * Add license sub menu page
     *
     * @param array $subMenus sub menus
     *
     * @return array
     */
    public static function licenseSubMenu($subMenus)
    {
        $subMenus[] = SettingsPageController::generateSubMenuItem(self::L2_SLUG_LICENSING, __('Licensing', 'duplicator-pro'), '', true, 100);
        return $subMenus;
    }

    /**
     * Render license page
     *
     * @param string[] $currentLevelSlugs current page/tables slugs
     *
     * @return void
     */
    public static function renderLicenseContent($currentLevelSlugs)
    {
        switch ($currentLevelSlugs[1]) {
            case self::L2_SLUG_LICENSING:
                require ProBase::getAddonPath() . '/template/licensing.php';
                break;
        }
    }

    /**
     * License type viewer
     *
     * @return void
     */
    public static function displayLicenseInfo()
    {
        $license_type = License::getType();

        if ($license_type === License::TYPE_UNLICENSED) {
            echo sprintf('<b>%s</b>', DUP_PRO_U::__("Unlicensed"));
        } else {
            $global  = DUP_PRO_Global_Entity::get_instance();
            $license_limit = $global->license_limit;
            $license_data = License::getLicenseData(false);
            $license_key = License::getLicenseKey();

            if (License::isValidOvrKey($license_key)) {
                $license_key = License::getStandardKeyFromOvrKey($license_key);
            }

            switch ($license_type) {
                case License::TYPE_PERSONAL:
                    $license_text = DUP_PRO_U::__('Personal');
                    $supports_powertools = false;
                    $supports_mu_plus = false;
                    break;

                case License::TYPE_FREELANCER:
                    $license_text = DUP_PRO_U::__('Freelancer');
                    $supports_powertools = true;
                    $supports_mu_plus = false;
                    break;

                case License::TYPE_BUSINESS_GOLD:
                    if ($license_data->expires == 'lifetime') {
                        $license_text = DUP_PRO_U::__('Gold');
                    } else {
                        $license_text = DUP_PRO_U::__('Business');
                    }

                    $supports_powertools = true;
                    $supports_mu_plus = true;
                    $license_limit = 'Unlimited';
                    break;

                default:
                    $license_text = DUP_PRO_U::__('Unknown');
            }

            echo sprintf('<b>%s</b>', $license_text);

            $pt = $supports_powertools ? '<i class="far fa-check-circle"></i>  ' : '<i class="far fa-circle"></i>  ';
            $mup = $supports_mu_plus ? '<i class="far fa-check-circle"></i>  ' : '<i class="far fa-circle"></i>  ';

            $txt_lic_hdr = DUP_PRO_U::__('Site Licenses');
            $txt_lic_msg = DUP_PRO_U::__(
                'Indicates the number of sites the plugin can be active on at any one time. ' .
                'At any point you may deactivate/uninstall the plugin to free up the license and use the plugin elsewhere if needed.'
            );
            $txt_pt_hdr = DUP_PRO_U::__('Powertools');
            $txt_pt_msg = DUP_PRO_U::__('Enhanced features that greatly improve the productivity of serious users. Include hourly schedules, ' .
                                                'installer branding, salt & key replacement, priority support and more.');
            $txt_mup_hdr = DUP_PRO_U::__('Multisite Plus+');
            $txt_mup_msg = DUP_PRO_U::__(
                'Adds the ability to install a subsite as a standalone site, ' .
                'insert a standalone site into a multisite, or insert a subsite from the same/different multisite into a multisite.'
            );

            if ($license_data->expires != 'lifetime') {
                self::displayUpgrade($license_type, $license_key);
            }

            $lic_limit = is_numeric($license_limit) ? $license_limit : "<i style='color: #888888' class='fas fa-infinity'></i>";
            $site_count = is_numeric($license_data->site_count) ? $license_data->site_count : '?';

            echo '<div class="dup-license-type-info">';
            echo "<i class='far fa-check-circle'></i>  {$txt_lic_hdr} - {$site_count} of {$lic_limit} " .
                "<i class='fa fa-question-circle  fa-sm' data-tooltip-title='{$txt_lic_hdr}' data-tooltip='{$txt_lic_msg}'></i><br/>";
            echo $pt;
            echo "{$txt_pt_hdr} <i class='fa fa-question-circle fa-sm' data-tooltip-title='{$txt_pt_hdr}' data-tooltip='{$txt_pt_msg}'></i><br/>";
            echo $mup;
            echo "{$txt_mup_hdr} <i class='fa fa-question-circle fa-sm' data-tooltip-title='{$txt_mup_hdr}' data-tooltip='{$txt_mup_msg}'></i><br/>";
            echo '</div>';
        }
    }

    /**
     * Display the upgrade portion of the license information
     *
     * @param int    $license_type License type
     *
     * @param string $license_key  License key
     *
     * @return void
     */
    private static function displayUpgrade($license_type, $license_key)
    {
        $upgrade_url_template = 'https://snapcreek.com/checkout/?nocache=true&edd_action=sc_sl_license_upgrade&license_key=%1$s&upgrade_id=%2$d';
        $toggle = "jQuery('#sc_upgrade').toggle(); return false;";

        echo " <a href='#' onclick=\"{$toggle}\">[" . DUP_PRO_U::__('upgrade') . ']';
        echo '<div style="padding:10px; display:none;" id="sc_upgrade">';

        if ($license_type < License::TYPE_FREELANCER) {
            $upgrade_url = sprintf("$upgrade_url_template", $license_key, 1);

            $freelancer_header = DUP_PRO_U::__('Freelancer License');
            $freelancer_help = DUP_PRO_U::__('Increases site limit to 15 and adds Powertools.');
            $tooltip_text = "<i class='fa fa-question-circle fa-sm' data-tooltip-title='{$freelancer_header}' " .
                            "data-tooltip='{$freelancer_help}'></i>";

            echo "<a target='_blank' href='{$upgrade_url}'>" . DUP_PRO_U::__('Upgrade to Freelancer') . "</a> {$tooltip_text} <br/>";
        }

        if ($license_type < License::TYPE_BUSINESS_GOLD) {
            $upgrade_url = sprintf("$upgrade_url_template", $license_key, 2);
            $business_header = DUP_PRO_U::__('Business License');
            $business_help = DUP_PRO_U::__('Removes site limit. Also includes Powertools and the ability to import subsites and standalone sites ' .
                                           'into a multisite.');
            $tooltip_text = "<i class='fa fa-question-circle fa-sm' data-tooltip-title='{$business_header}' data-tooltip='{$business_help}'></i>";

            echo "<a target='_blank' href='{$upgrade_url}'>" . DUP_PRO_U::__('Upgrade to Business') . "</a> {$tooltip_text} <br/>";
        }

        $upgrade_url = sprintf("$upgrade_url_template", $license_key, 5);
        $gold_header = DUP_PRO_U::__('Gold License');
        $gold_help = DUP_PRO_U::__('Equivalent to Business license with lifetime support and updates.');
        $tooltip_text = "<i class='fa fa-question-circle fa-sm' data-tooltip-title='{$gold_header}' data-tooltip='{$gold_help}'></i>";

        echo "<a target='_blank' href='{$upgrade_url}'>" . DUP_PRO_U::__('Upgrade to Gold') . "</a> {$tooltip_text} <br/>";
        echo '<small>* ' . DUP_PRO_U::__('Full credit is given for existing license when upgrading.') . '</small><br/>';
        echo '</div>';
    }
}
