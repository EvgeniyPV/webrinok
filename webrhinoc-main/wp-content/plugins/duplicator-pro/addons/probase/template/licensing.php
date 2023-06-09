<?php
/* @var $global DUP_PRO_Global_Entity */

defined("ABSPATH") or die("");

use Duplicator\Addons\ProBase\License\License;
use Duplicator\Addons\ProBase\LicensingController;

DUP_PRO_U::hasCapability('manage_options');

$global  = DUP_PRO_Global_Entity::get_instance();
$sglobal = DUP_PRO_Secure_Global_Entity::getInstance();

$nonce_action                = 'duppro-settings-licensing-edit';
$error_response              = null;
$action_response             = null;
$license_activation_response = null;
$is_localhost                = strstr($_SERVER['HTTP_HOST'], 'localhost');

//SAVE RESULTS
if (isset($_POST['action'])) {
    $action = sanitize_text_field($_POST['action']);
    switch ($action) {
        case 'activate':
            DUP_PRO_U::verifyNonce($_POST['_wpnonce'], 'duplicator-pro-licence');

            /**
             * If license isn't visible input is always disabled
             */
            if ($global->license_key_visible) {
                $submitted_license_key = trim($_REQUEST['_license_key']);
            } else {
                $submitted_license_key = get_option(License::LICENSE_KEY_OPTION_NAME);
            }

            if (License::isValidOvrKey($submitted_license_key)) {
                License::setOvrKey($submitted_license_key);
            } else {
                if (preg_match('/^[a-f0-9]{32}$/i', $submitted_license_key)) {
                    update_option(License::LICENSE_KEY_OPTION_NAME, $submitted_license_key);
                    $license_activation_response = License::changeLicenseActivation(true);

                    switch ($license_activation_response) {
                        case License::ACTIVATION_RESPONSE_OK:
                            $action_response = DUP_PRO_U::__("License Activated");
                            break;
                        case License::ACTIVATION_RESPONSE_POST_ERROR:
                            $error_response = sprintf(
                                __(
                                    "Cannot communicate with snapcreek.com. " .
                                    "Please see <a target='_blank' href='%s'>this FAQ entry</a> for possible causes and resolutions.",
                                    'duplicator-pro'
                                ),
                                'https://snapcreek.com/duplicator/docs/faqs-tech/#faq-licensing-005-q'
                            );
                            break;
                        case License::ACTIVATION_RESPONSE_INVALID:
                        default:
                            $error_response = DUP_PRO_U::__('Error activating license.');
                            break;
                    }
                } else {
                    $error_response = DUP_PRO_U::__('Please enter a valid key. Key should be 32 characters long.');
                }
            }
            break;

        case 'deactivate':
        case 'clear_key':
            DUP_PRO_U::verifyNonce($_POST['_wpnonce'], 'duplicator-pro-licence');
            if (License::isValidOvrKey(License::getLicenseKey())) {
                // Reset license key otherwise will be artificially stuck on as valid
                update_option(License::LICENSE_KEY_OPTION_NAME, '');
            } else {
                $license_activation_response = License::changeLicenseActivation(false);

                switch ($license_activation_response) {
                    case License::ACTIVATION_RESPONSE_OK:
                        $action_response = DUP_PRO_U::__("License Deactivated");
                        break;

                    case License::ACTIVATION_RESPONSE_POST_ERROR:
                        $error_response = sprintf(
                            __(
                                "Cannot communicate with snapcreek.com. " .
                                "Please see <a target='_blank' href='%s'>this FAQ entry</a> for possible causes and resolutions.",
                                'duplicator-pro'
                            ),
                            'https://snapcreek.com/duplicator/docs/faqs-tech/#faq-licensing-005-q'
                        );
                        break;

                    case License::ACTIVATION_RESPONSE_INVALID:
                    default:
                        $error_response = DUP_PRO_U::__('Error deactivating license.');
                        break;
                }
            }


            if ($action == 'clear_key') {
                update_option(License::LICENSE_KEY_OPTION_NAME, '');

                $global->license_key_visible = true;
                $sglobal->lkp                = '';

                $global->save();
                $sglobal->save();
            }



            break;

        case 'hide_key':
            DUP_PRO_U::verifyNonce($_POST['_wpnonce'], 'duplicator-pro-licence');

            $password              = sanitize_text_field($_POST['_key_password']);
            $password_confirmation = sanitize_text_field($_POST['_key_password_confirmation']);

            if (empty($password)) {
                $error_response = DUP_PRO_U::__('Password cannot be empty.');
            } else {
                if ($password == $password_confirmation) {
                    $global->license_key_visible = false;
                    $sglobal->lkp                = $password;
                    $global->save();
                    $sglobal->save();
                    $action_response             = DUP_PRO_U::__("Key now hidden.");
                } else {
                    $error_response = DUP_PRO_U::__("Passwords don't match.");
                }
            }
            break;

        case 'show_key':
            DUP_PRO_U::verifyNonce($_POST['_wpnonce'], 'duplicator-pro-licence');
            $password = sanitize_text_field($_POST['_key_password']);

            if ($password == $sglobal->lkp) {
                $global->license_key_visible = true;
                $sglobal->lkp                = '';

                $global->save();
                $sglobal->save();

                $action_response = DUP_PRO_U::__("Key now visible.");
            } else {
                $error_response = DUP_PRO_U::__("Wrong password entered. Key remains hidden.");
            }

            break;
    }
}

$license_status          = License::getLicenseStatus(true);
$license_type            = License::getType();
$license_text_disabled   = false;
$activate_button_text    = DUP_PRO_U::__('Activate');
$license_status_text_alt = '';

if ($license_status == License::STATUS_VALID) {
    $license_status_style  = 'color:#509B18';
    $activate_button_text  = DUP_PRO_U::__('Deactivate');
    $license_text_disabled = true;

    $license_key           = License::getLicenseKey();

    if (License::isValidOvrKey($license_key)) {
        $standard_key        = License::getStandardKeyFromOvrKey($license_key);
        $license_status_text = DUP_PRO_U::__("Status: Active (Using license override for key {$standard_key})");
    } else {
        $license_status_text = '<b>' . DUP_PRO_U::__('Status: ') . '</b>' . DUP_PRO_U::__('Active');
        $license_status_text .= '<br/>';
        $license_status_text .= '<b>' . DUP_PRO_U::__('Expiration: ') . '</b>';
        $license_status_text .= License::getExpirationDate(get_option('date_format'));
        $expDays = License::getExpirationDays();
        if ($expDays == 0) {
            $expDays = __('expired', 'duplicator-pro');
        } elseif ($expDays == PHP_INT_MAX) {
            $expDays = __('no expiration', 'duplicator-pro');
        } else {
            $expDays = $expDays . ' ' . DUP_PRO_U::__('days left');
        }
        $license_status_text .= ' (<b>' . $expDays . '</b>)';
    }

    //INACTIVE
} elseif (($license_status == License::STATUS_INACTIVE)) {
    $license_status_style = 'color:#dd3d36;';
    $license_status_text  = DUP_PRO_U::__('Status: Inactive');

    //SITE-INACTIVE
} elseif ($license_status == License::STATUS_SITE_INACTIVE) {
    $license_status_style = 'color:#dd3d36;';
    $global               = DUP_PRO_Global_Entity::get_instance();

    if ($license_type == License::TYPE_BUSINESS_GOLD) {
        $license_status_text = ($global->license_no_activations_left) ?
                            sprintf(
                                __(
                                    'Status: Inactive (out of site licenses).' .
                                    '<br/> Business/Gold site licenses are granted in batches of 500.'
                                    . ' Please submit a %1sticket request%2s and we will grant you another batch.'
                                    . '<br/>This process helps to ensure that licenses are not stolen or abused for users.',
                                    'duplicator-pro'
                                ),
                                '<a href="https://snapcreek.com/ticket/index.php?a=add&category=1" target="_blank">',
                                '</a>'
                            ) :
                            __('Status: Inactive', 'duplicator-pro');
    } else {
        $license_status_text  = ($global->license_no_activations_left) ?
            __(
                'Status: Inactive (out of site licenses).' .
                '<br/> Use the link above to login to your snapcreek.com dashboard to manage your licenses or upgrade to a higher license.',
                'duplicator-pro'
            ) :
            __('Status: Inactive', 'duplicator-pro');
    }

    //EXPIRED
} elseif ($license_status == License::STATUS_EXPIRED) {
    $renewal_url          = 'https://snapcreek.com/checkout?edd_license_key=' . License::getLicenseKey();
    $license_status_style = 'color:#dd3d36;';
    $license_status_text  = sprintf(
        __(
            'Your Duplicator Pro license key has expired so you aren\'t getting important updates! ' .
            '<a target="_blank" href="%1$s">Renew your license now</a>',
            'duplicator-pro'
        ),
        $renewal_url
    );

    //DEFAULT
} else {
    $license_status_string   = License::getLicenseStatusString($license_status);
    $license_status_style    = 'color:#dd3d36;';
    $license_status_text     = '<b>' .  DUP_PRO_U::__('Status: ') . '</b>' . $license_status_string . '<br/>';
    $license_status_text_alt = DUP_PRO_U::__('If license activation fails please wait a few minutes and retry.');
    $license_status_text_alt .= '<div class="dup-license-status-notes ">';
    $license_status_text_alt .= sprintf(
        DUP_PRO_U::__('- Failure to activate after several attempts please review %1$sfaq activation steps%2$s'),
        '<a target="_blank" href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-manage-005-q">',
        '</a>.<br/>'
    );
    $license_status_text_alt .= sprintf(
        __('- To upgrade or renew your license visit %1$ssnapcreek.com%2$s', 'duplicator-pro'),
        '<a target="_blank" href="https://snapcreek.com">',
        '</a>.<br/>'
    );
     $license_status_text_alt .= '- A valid key is needed for plugin updates but not for functionality.</div>';
}
?>

<form 
    id="dup-settings-form" 
    action="<?php echo self_admin_url('admin.php?page=' . DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG); ?>" 
    method="post" 
    data-parsley-validate
>
    <?php // wp_nonce_field($nonce_action);
    ?>
    <input type="hidden" name="action" value="save" id="action">
    <input type="hidden" name="page" value="<?php echo DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG ?>">
    <input type="hidden" name="tab" value="licensing">

    <?php if ($action_response != null) : ?>
        <div class="notice notice-success is-dismissible dpro-wpnotice-box">
            <p><?php echo $action_response; ?></p>
        </div>
    <?php endif; ?>

    <?php if ($error_response != null) : ?>
        <div class="notice notice-error is-dismissible dpro-wpnotice-box">
            <p><?php echo $error_response; ?></p>
        </div>
    <?php endif; ?>

    <h3 class="title"><?php DUP_PRO_U::esc_html_e("Activation") ?> </h3>
    <hr size="1" />
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php DUP_PRO_U::esc_html_e("Dashboard") ?></th>
            <td>
                <?php
                echo sprintf(
                    DUP_PRO_U::__('%1$sManage Account Online%2$s'),
                    '<i class="fa fa-th-large fa-sm"></i> <a target="_blank" href="https://snapcreek.com/dashboard"> ',
                    '</a>'
                );
                ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php DUP_PRO_U::esc_html_e("License Type") ?></th>
            <td class="dup-license-type">
                <?php
                    LicensingController::displayLicenseInfo();
                ?>
            </td>            
        </tr>

        <tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::esc_html_e("License Key"); ?></label></th>
            <td class="dup-license-key-area">
                <input
                    type="<?php echo $global->license_key_visible ? 'text' : 'password'; ?>"
                    class="dup-license-key-input"
                    name="_license_key"
                    id="_license_key"
                    <?php DUP_PRO_UI::echoDisabled($license_text_disabled || !$global->license_key_visible); ?>
                    value="<?php echo $global->license_key_visible ? License::getLicenseKey() : '**********************'; ?>">
                <br />
                <p class="description">
                    <?php
                    echo "<span style='$license_status_style'>$license_status_text</span>";
                    echo $license_status_text_alt;
                    ?>
                </p>
                <?php $echostring = (($license_status != License::STATUS_VALID) ? 'true' : 'false'); ?>

                <div class="dup-license-key-btns">
                    <button
                        class="button"
                        onclick="DupPro.Licensing.ChangeActivationStatus(<?php echo $echostring; ?>);return false;">
                        <?php echo $activate_button_text; ?>
                    </button>
                    <button class="button" onclick="DupPro.Licensing.ClearActivationStatus();return false;">
                        <?php DUP_PRO_U::esc_html_e("Clear Key") ?>
                    </button>
                </div>
            </td>
        </tr>


    </table>

    <h3 class="title"><?php DUP_PRO_U::esc_html_e("Key Visibility") ?> </h3>
    <small>
        <?php
        DUP_PRO_U::esc_html_e(
            "This is an optional setting that prevents the 'License Key' from being copied. " .
            "Enter a password and hit the 'Hide Key' button."
        );
        echo '<br/>';
        DUP_PRO_U::esc_html_e("To show the 'License Key' and allow for it to be copied to your clipboard enter in the password and hit the 'Show Key' button.");
        ?>
    </small>
    <hr size="1" />
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Password"); ?></label></th>
            <td>
                <input type="password" class="wide-input" name="_key_password" id="_key_password" size="50" />
            </td>
        </tr>
        <tr style="display:<?php echo $global->license_key_visible ? 'table-row' : 'none'; ?>" valign="top">
            <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Retype Password"); ?></label></th>
            <td>
                <input 
                    type="password" 
                    class="wide-input" 
                    name="_key_password_confirmation" 
                    id="_key_password_confirmation" 
                    data-parsley-equalto="#_key_password" 
                    size="50" 
                >
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"></th>
            <td>
                <?php
                wp_nonce_field('duplicator-pro-licence');
                ?>
                <button 
                    class="button" 
                    id="show_hide" 
                    onclick="DupPro.Licensing.ChangeKeyVisibility(<?php DUP_PRO_UI::echoBoolean(!$global->license_key_visible); ?>); return false;"
                >
                    <?php echo $global->license_key_visible ? DUP_PRO_U::__('Hide Key') : DUP_PRO_U::__('Show Key'); ?>
                </button>
            </td>
        </tr>
    </table>
</form>

<script>
    jQuery(document).ready(function($) {
        DupPro.Licensing = new Object();

        // Ensure if they hit enter in one of the password boxes the correct action takes place
        $("#_key_password, #_key_password_confirmation").keyup(function(event) {

            if (event.keyCode == 13) {
                $("#show_hide").click();
            }
        });

        DupPro.Licensing.ChangeActivationStatus = function(activate) {
            if (activate) {
                $('#action').val('activate');
            } else {
                $('#action').val('deactivate');
            }
            $('#dup-settings-form').submit();
        }

        DupPro.Licensing.ClearActivationStatus = function() {
            $('#action').val('clear_key');
            $('#dup-settings-form').submit();
        }

        DupPro.Licensing.ChangeKeyVisibility = function(show) {
            if (show) {
                $('#action').val('show_key');
            } else {
                $('#action').val('hide_key');
            }
            $('#dup-settings-form').submit();
        }

        DupPro.Licensing.ToggleUnlimited = function() {
            $('#unlmtd-lic-text').toggle();
        }
    });
</script>
