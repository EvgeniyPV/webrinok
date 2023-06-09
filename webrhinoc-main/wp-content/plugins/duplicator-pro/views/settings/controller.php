<?php
defined("ABSPATH") or die("");

DUP_PRO_Handler::init_error_handler();

global $wpdb;
global $wp_version;

$current_tab = isset($_REQUEST['tab']) ? sanitize_text_field($_REQUEST['tab']) : 'general';
?>

<style>
    .narrow-input {
        width: 80px;
    }

    .wide-input {
        width: 400px;
    }

    i.description {
        font-size: 12px
    }

    table.form-table tr td {
        padding-top: 15px;
    }

    td.dup-license-type div {
        padding: 5px 0 0 30px
    }

    td.dup-license-type i.fa-check-circle {
        display: inline-block;
        padding-right: 5px
    }

    td.dup-license-type i.fa-circle {
        display: inline-block;
        padding-right: 7px
    }

    td.dup-license-type i.fa-question-circle {
        font-size: 12px
    }

    div.sub-opts {
        padding: 10px 0 5px 30px;
    }

    h3.title {
        padding: 0;
        margin: 5px 0 0 0
    }

    div.wrap form {
        padding-top: 15px
    }

    p.dpro-save-submit {
        margin: 10px 0px 0xp 5px;
    }

    p.description {
        max-width: 700px
    }
</style>

<h2 class="nav-tab-wrapper">
    <a href="?page=duplicator-pro-settings&tab=general" id="dpro-settings-tab-general-link" class="nav-tab <?php echo ($current_tab == 'general') ? 'nav-tab-active' : '' ?>"> <?php DUP_PRO_U::esc_html_e('General'); ?></a>
    <a href="?page=duplicator-pro-settings&tab=package" id="dpro-settings-tab-package-link" class="nav-tab <?php echo ($current_tab == 'package') ? 'nav-tab-active' : '' ?>"> <?php DUP_PRO_U::esc_html_e('Packages'); ?></a>
    <a href="?page=duplicator-pro-settings&tab=schedule" id="dpro-settings-tab-schedule-link" class="nav-tab <?php echo ($current_tab == 'schedule') ? 'nav-tab-active' : '' ?>"> <?php DUP_PRO_U::esc_html_e('Schedules'); ?></a>
    <a href="?page=duplicator-pro-settings&tab=storage" id="dpro-settings-tab-storage-link" class="nav-tab <?php echo ($current_tab == 'storage') ? 'nav-tab-active' : '' ?>"> <?php DUP_PRO_U::esc_html_e('Storage'); ?></a>
    <a href="?page=duplicator-pro-settings&tab=import" id="dpro-settings-tab-import-link" class="nav-tab <?php echo ($current_tab == 'import') ? 'nav-tab-active' : '' ?>"> <?php DUP_PRO_U::esc_html_e('Import'); ?></a>
    <a href="?page=duplicator-pro-settings&tab=licensing" id="dpro-settings-tab-licencing-link" class="nav-tab <?php echo ($current_tab == 'licensing') ? 'nav-tab-active' : '' ?>"> <?php DUP_PRO_U::esc_html_e('Licensing'); ?></a>
</h2>

<?php
switch ($current_tab) {
    case 'general':
        include(dirname(__FILE__) . '/general/main.php');
        break;
    case 'package':
        include(dirname(__FILE__) . '/package/main.php');
        break;
    case 'import':
        include(dirname(__FILE__) . '/import.php');
        break;
    case 'schedule':
        include(dirname(__FILE__) . '/schedule.php');
        break;
    case 'storage':
        DUP_PRO_CTRL_Storage_Setting::controller();
        break;
    case 'licensing':
        include(dirname(__FILE__) . '/licensing.php');
        break;
}
require_once DUPLICATOR_PRO_PLUGIN_PATH . '/views/parts/ajax-loader.php';
