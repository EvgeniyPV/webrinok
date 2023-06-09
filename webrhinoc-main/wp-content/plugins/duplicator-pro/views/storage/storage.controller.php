<?php
defined("ABSPATH") or die("");

use Duplicator\Libs\Snap\SnapURL;

DUP_PRO_U::hasCapability('export');
?>
<style>
    div#dup-store-err-details {display:none}
</style>
<?php

$profile_url = DUP_PRO_U::getMenuPageURL(DUP_PRO_Constants::$STORAGE_SUBMENU_SLUG, false);
$storage_tab_url = SnapURL::appendQueryValue($profile_url, 'tab', 'storage');

$edit_storage_url = SnapURL::appendQueryValue($storage_tab_url, 'inner_page', 'edit');
$edit_default_storage_url = SnapURL::appendQueryValue($storage_tab_url, 'inner_page', 'edit-default');

$inner_page = isset($_REQUEST['inner_page']) ? sanitize_text_field($_REQUEST['inner_page']) : 'storage';

/**
 *
 * @param Exception $e
 * @return string
 */
function getDupProStorageErrorMsg($e)
{
    $settings_url = DUP_PRO_U::getMenuPageURL(DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG, false);

    $storage_error_msg = '<div class="error-txt" style="margin:10px 0 20px 0; max-width:750px">';
    $storage_error_msg .= DUP_PRO_U::esc_html__('An error has occurred while trying to read a storage item!  ');
    $storage_error_msg .= DUP_PRO_U::esc_html__('To resolve this issue delete the storage item and re-enter its information.  ');
    $storage_error_msg .= '<br/><br/>';
    $storage_error_msg .= DUP_PRO_U::esc_html__('This problem can be due to a security plugin changing keys in wp-config.php, causing the storage information to become unreadable.  ');
    $storage_error_msg .= DUP_PRO_U::esc_html__('If such a plugin is doing this then either disable the key changing functionality in the security plugin or go to ');
    $storage_error_msg .= "<a href='{$settings_url}'>";
    $storage_error_msg .= DUP_PRO_U::esc_html__('Duplicator Pro > Settings');
    $storage_error_msg .= '</a>';
    $storage_error_msg .= DUP_PRO_U::esc_html__(' and disable settings encryption.  ');
    $storage_error_msg .= '<br/><br/>';
    $storage_error_msg .= DUP_PRO_U::esc_html__('If the problem persists after doing these things then please contact the support team.');
    $storage_error_msg .= '</div>';
    $storage_error_msg .= '<a href="javascript:void(0)" onclick="jQuery(\'#dup-store-err-details\').toggle();">';
    $storage_error_msg .= DUP_PRO_U::esc_html__('Show Details');
    $storage_error_msg .= '</a>';
    $storage_error_msg .= '<div id="dup-store-err-details" >' . esc_html($e->getMessage()) .
        "<br/><br/><small>" .
        esc_html($e->getTraceAsString()) .
        "</small></div>";
    return $storage_error_msg;
}

try {
    switch ($inner_page) {
        case 'storage':
            // I left the global try catch for security but the exceptions should be managed inside the list.
            include(DUPLICATOR____PATH . '/views/storage/storage.list.php');
            break;
        case 'edit':
            include(DUPLICATOR____PATH . '/views/storage/storage.edit.php');
            break;
        case 'edit-default':
            include(DUPLICATOR____PATH . '/views/storage/storage.edit.default.php');
            break;
    }
} catch (Exception $e) {
    echo getDupProStorageErrorMsg($e);
}