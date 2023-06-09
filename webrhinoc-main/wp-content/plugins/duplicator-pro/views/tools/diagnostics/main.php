<?php
defined("ABSPATH") or die("");
wp_enqueue_script('dup-handlebars');

require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/class.scan.check.php');

global $wp_version;
global $wpdb;

$action_response  = null;
$diagnosticAction = false;

$txt_found     = DUP_PRO_U::__("Found");
$txt_not_found = DUP_PRO_U::__("Removed");

$view_state         = DUP_PRO_UI_ViewState::getArray();
$ui_css_srv_panel   = (isset($view_state['dup-settings-diag-srv-panel']) && $view_state['dup-settings-diag-srv-panel']) ? 'display:block' : 'display:none';
$ui_css_data_panel  = (isset($view_state['dup-settings-diag-opts-panel']) && $view_state['dup-settings-diag-opts-panel']) ? 'display:block' : 'display:none';
$ui_css_data_panel  = (isset($_GET['orphanpurge']) && $_GET['orphanpurge'] == '1') ? 'display:block' : $ui_css_data_panel;
$orphaned_filepaths = DUP_PRO_Server::getOrphanedPackageFiles();
$scan_run           = (isset($_POST['action']) && $_POST['action'] == 'duplicator_recursion') ? true : false;
$home_path          = duplicator_pro_get_home_path();

$long_installer_path = (isset($_GET['in'])) ? $home_path . '/' . sanitize_text_field($_GET['in']) : '';

//POST BACK
$action_updated     = null;
$_REQUEST['action'] = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'display';

if (isset($_REQUEST['action'])) {
    $diagnosticAction = $_REQUEST['action'];
    switch ($_REQUEST['action']) {
        case 'duplicator_pro_tools':
            $action_response  = DUP_PRO_U::__('Plugin settings reset.');
            break;
        case 'duplicator_pro_ui_view_state':
            $action_response  = DUP_PRO_U::__('View state settings reset.');
            break;
        case 'duplicator_pro_package_active':
            $action_response  = DUP_PRO_U::__('Active package settings reset.');
            break;
        case 'purge-orphans':
            $action_response  = DUP_PRO_U::__('Cleaned up orphaned package files!');
            break;
        case 'tmp-cache':
            DUP_PRO_Package::tmp_cleanup(true);
            $action_response  = DUP_PRO_U::__('Build cache removed.');
            break;
        default:
            $diagnosticAction = false;
            $action_response  = null;
    }
}
?>

<style>
<?php echo isset($css_hide_msg) ? $css_hide_msg : ''; ?>
    div#message {margin:0px 0px 10px 0px}
    td.dpro-settings-diag-header {background-color:#D8D8D8; font-weight: bold; border-style: none; color:black}
    table.widefat th {font-weight:bold; }
    table.widefat td {padding:2px 2px 2px 8px; }
    table.widefat td:nth-child(1) {width:10px;}
    table.widefat td:nth-child(2) {padding-left: 20px; width:100% !important}
    textarea.dup-opts-read {width:100%; height:40px; font-size:12px}
    button.dpro-store-fixed-btn {min-width: 165px; text-align: center}
    div.success,span.success {color:#4A8254}
    div.failed {color:red}
    table.dpro-reset-opts td:first-child {font-weight: bold}
    table.dpro-reset-opts td {padding:4px}
    div#dpro-tools-delete-moreinfo {display: none; padding: 5px 0 0 20px; border:1px solid #dfdfdf;  border-radius: 5px; padding:10px; margin:5px; width:98% }
    div#dpro-tools-delete-orphans-moreinfo {display: none; padding: 5px 0 0 20px; border:1px solid #dfdfdf;  border-radius: 5px; padding:10px; margin:5px; width:98% }

    /*PHP_INFO*/
    div#dpro-phpinfo {padding:10px 5px;}
    div#dpro-phpinfo table {padding:1px; background:#dfdfdf; -webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px; width:100% !important; box-shadow:0 8px 6px -6px #777;}
    div#dpro-phpinfo td, th {padding:3px; background:#fff; -webkit-border-radius:2px;-moz-border-radius:2px;border-radius:2px;}
    div#dpro-phpinfo tr.h img {display:none;}
    div#dpro-phpinfo tr.h td {background:none;}
    div#dpro-phpinfo tr.h th {text-align:center; background-color:#efefef;}
    div#dpro-phpinfo td.e {font-weight:bold}
</style>
