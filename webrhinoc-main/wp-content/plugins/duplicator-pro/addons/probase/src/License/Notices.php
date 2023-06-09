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

class Notices
{
    const OPTION_KEY_EXPIRED_LICENCE_NOTICE_DISMISS_TIME = 'duplicator_pro_expired_licence_notice_time';
    const EXPIRED_LICENCE_NOTICE_DISMISS_FOR_DAYS        = 14;

    /**
     * Init notice actions
     *
     * @return void
     */
    public static function init()
    {
        add_action('admin_init', array(__CLASS__, 'adminInit'));
    }

    /**
     * Function called on hook admin_init
     *
     * @return void
     */
    public static function adminInit()
    {
        $action = is_multisite() ? 'network_admin_notices' : 'admin_notices';
        add_action($action, array(__CLASS__, 'licenseAlertCheck'));
    }

    /**
     * Used by the WP action hook to detect the state of the endpoint license
     * which calls the various show* methods for which alert to display
     *
     * @return null
     */
    public static function licenseAlertCheck()
    {
        $on_licensing_tab = (isset($_REQUEST['tab']) && ($_REQUEST['tab'] === 'licensing'));

        if ($on_licensing_tab === false) {
            if (!file_exists(DUPLICATOR_PRO_SSDIR_PATH . "/ovr.dup")) {
                //Style needs to be loaded here because css is global across wp-admin
                wp_enqueue_style('dup-pro-plugin-style-notices', DUPLICATOR_PRO_PLUGIN_URL . 'assets/css/admin-notices.css', null, DUPLICATOR_PRO_VERSION);

                try {
                    $license_status = License::getLicenseStatus(false);
                } catch (\Exception $ex) {
                    \DUP_PRO_Log::traceError("Could not get license status.");
                    return false;
                }

                if ($license_status === License::STATUS_EXPIRED) {
                    $expired_licence_notice_dismiss_time = get_option(self::OPTION_KEY_EXPIRED_LICENCE_NOTICE_DISMISS_TIME, false);
                    if (
                        false === $expired_licence_notice_dismiss_time ||
                        (time() - $expired_licence_notice_dismiss_time) > (DAY_IN_SECONDS * self::EXPIRED_LICENCE_NOTICE_DISMISS_FOR_DAYS)
                    ) {
                        self::showExpired();
                    }
                } elseif ($license_status !== License::STATUS_VALID) {
                    $global = \DUP_PRO_Global_Entity::get_instance();

                    if ($global->license_no_activations_left) {
                        self::showNoActivationsLeft();
                    } else {
                        $days_invalid = floor((time() - $global->initial_activation_timestamp) / 86400);

                        // If an md5 is present always do standard nag
                        $license_key = get_option(License::LICENSE_KEY_OPTION_NAME, '');
                        $md5_present = \DUP_PRO_Low_U::isValidMD5($license_key);

                        if ($md5_present || ($days_invalid < License::UNLICENSED_SUPER_NAG_DELAY_IN_DAYS)) {
                            self::showInvalidStandardNag();
                        } else {
                            self::showInvalidSuperNag($days_invalid);
                        }
                    }
                }
            }
        }
    }

    /**
     * Shows the smaller standard nag screen
     *
     * @return string   HTML alert message hook
     */
    private static function showInvalidStandardNag()
    {
        $img_url           = plugins_url('duplicator-pro/assets/img/warning.png');
        $licensing_tab_url = self_admin_url() . "admin.php?page=" . \DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG . '&tab=licensing';

        $problem_text = 'missing';

        if (get_option(License::LICENSE_KEY_OPTION_NAME, '') !== '') {
            $problem_text = 'invalid or disabled';
        }

        echo "<div class='update-nag dpro-admin-notice'><p><img src='{$img_url}' style='float:left; padding:0 10px 0 5px' /> " .
        "<b>Warning!</b> Your Duplicator Pro license key is {$problem_text}... <br/>" .
        "This means this plugin does not have access to <b>security updates</b>, <i>bug fixes</i>, <b>support request</b> or <i>new features</i>.<br/>" .
        "<b>Please <a href='" . esc_url($licensing_tab_url) . "'>Activate Your License</a></b>.&nbsp; " .
        "If you do not have a license key go to " .
        "<a target='_blank' href='https://snapcreek.com/dashboard'>snapcreek.com</a> to get it.</p></div>";
    }

    /**
     * Shows the larger super nag screen used for display after the trial period
     *
     * @param int $daysInvalid The number of days the license has been invalid
     *
     * @return string   HTML alert message hook
     */
    private static function showInvalidSuperNag($daysInvalid)
    {
        $licensing_tab_url = esc_url(self_admin_url() . "admin.php?page=" . \DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG . '&tab=licensing');
        ?>
        <div class="update-nag dpro-admin-notice dpro-invalid-license">
            <h2>Invalid</h2>
            <p>
                <b>Bad News:</b> This Duplicator Pro License is Invalid. <br/>
                <b>Good News:</b> Get 10% Off Duplicator Pro Today! 
            </p>
            The Duplicator Pro plugin has been running for at least 30 days without a valid license.<br/>
            This means you don't have access to <b>security updates</b>, <i>bug fixes</i>, <b>support requests</b> or <i>new features</i>.<br/>
            <p>
                <a href="<?php echo $licensing_tab_url; ?>">Activate Your License Now!</a> <br/>
                - or - <br/>
                <a target='_blank' href='https://snapcreek.com/duplicator/pricing?discount=SUPERN_10_F2'>Purchase and Get 10% Off!*</a> <br/>
                <small>*Discount appears in cart at checkout time.</small>
            </p>
        </div>
        <?php
    }

    /**
     * Shows the license count used up alert
     *
     * @return string   HTML alert message hook
     */
    private static function showNoActivationsLeft()
    {
        $licensing_tab_url = self_admin_url() . "admin.php?page=" . \DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG . '&tab=licensing';
        $dashboard_url     = 'https://snapcreek.com/dashboard';
        $img_url           = plugins_url('duplicator-pro/assets/img/warning.png');

        echo '<div class="update-nag dpro-admin-notice" style="font-size:1.2rem">' .
        '<div style="text-align:center">' .
        "<img src='$img_url' style='/* float:left; */text-align: center;margin: auto;padding:0 10px 0 5px; width:80px'>" .
        '</div>' .
        '<p style="text-align: center;font-size: 2rem;line-height: 2.7rem; margin-top:10px">' .
        'Duplicator Pro\'s license is deactivated because you\'re out of site activations.</p>' .
        "<p style='text-align: center;font-size: 1.3rem; line-height: 2.2rem'>" .
        "Upgrade your license using the <a href='$dashboard_url' target='_blank'>Snap Creek Dashboard</a> or deactivate plugin on old sites.<br/>" .
        "After making necessary changes <a href='" . esc_url($licensing_tab_url) . "'>refresh the license status.</a>" .
        '</div>';
    }

    /**
     * Shows the expired message alert
     *
     * @return string   HTML alert message hook
     */
    private static function showExpired()
    {
        $license_key = get_option(License::LICENSE_KEY_OPTION_NAME, '');
        $renewal_url = 'https://snapcreek.com/checkout?edd_license_key=' . $license_key;
        $img_url     = plugins_url('duplicator-pro/assets/img/plug.png');

        $htmlMsg = "<img src='{$img_url}' style='float:left; padding:0 10px 0 5px' />" .
            "<b>Warning! Your Duplicator Pro license has expired...</b> <br/>" .
            "You're currently missing important updates for <b>security patches</b>, <i>bug fixes</i>, support requests, &amp; <u>new features</u>.<br/>" .
            "<a target='_blank' href='{$renewal_url}'>Renew now to receive a 40% discount off the current price!</a>";
        \DUP_PRO_UI_Notice::displayGeneralAdminNotice(
            $htmlMsg,
            \DUP_PRO_UI_Notice::GEN_ERROR_NOTICE,
            true,
            array(
                'duplicator-pro-admin-notice',
                'dpro-admin-notice'
            ),
            array(
                'data-to-dismiss' => self::OPTION_KEY_EXPIRED_LICENCE_NOTICE_DISMISS_TIME
            )
        );
    }
}
