<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Core\Views\TplMng;

require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/entities/class.system.global.entity.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/class.package.pagination.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/ui/class.ui.dialog.php');

global $packagesViewData;
$tplMng = TplMng::getInstance();

$packagesViewData = array(
    'is_freelancer_plus'            => \Duplicator\Addons\ProBase\License\License::isFreelancer(),
    'display_brand'                 => false,
    'pending_cancelled_package_ids' => DUP_PRO_Package::get_pending_cancellations(),
    'rowCount'                      => 0,
    'package_ui_created'            => null
);

if (isset($_REQUEST['create_from_temp'])) {
    //Takes temporary package and inserts it into the package table
    $package = DUP_PRO_Package::get_temporary_package(false);
    if ($package != null) {
        $package->save();
    }
    unset($_REQUEST['create_from_temp']);
    unset($package);
}

$system_global = DUP_PRO_System_Global_Entity::get_instance();

if (isset($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'stop-build') {
        $package_id     = (int) $_REQUEST['action-parameter'];
        DUP_PRO_LOG::trace("stop build of $package_id");
        $action_package = DUP_PRO_Package::get_by_id($package_id);
        if ($action_package != null) {
            DUP_PRO_LOG::trace("set $action_package->ID for cancel");
            $action_package->set_for_cancel();
        } else {
            DUP_PRO_LOG::trace(
                "could not find package so attempting hard delete. "
                . "Old files may end up sticking around although chances are there isnt much if we couldnt nicely cancel it."
            );
            $result = DUP_PRO_Package::force_delete($package_id);
            ($result) ? DUP_PRO_LOG::trace("Hard delete success") : DUP_PRO_LOG::trace("Hard delete failure");
        }
        unset($action_package);
    } elseif ($_REQUEST['action'] == 'clear-messages') {
        $system_global->clear_recommended_fixes();
        $system_global->save();
    }
}

$totalElements = $wpdb->get_var("SELECT count(id) as totalElements FROM `{$wpdb->base_prefix}duplicator_pro_packages`");
$statusActive  = $wpdb->get_var("SELECT count(id) as totalElements FROM `{$wpdb->base_prefix}duplicator_pro_packages`  WHERE status < 100 and status > 0");

$pager        = new DUP_PRO_Package_Pagination();
$per_page     = $pager->get_per_page();
$current_page = ($statusActive >= 1) ? 1 : $pager->get_pagenum();
$offset       = ($current_page - 1) * $per_page;

$global                 = DUP_PRO_Global_Entity::get_instance();

$orphan_info        = DUP_PRO_Server::getOrphanedPackageInfo();
$orphan_display_msg = $orphan_info['count'];

$recommended_text_fix_present           = false;
$user_id                                = get_current_user_id();
$creaderFormat                          = get_user_meta($user_id, 'duplicator_pro_created_format', true);
$packagesViewData['package_ui_created'] = is_numeric($creaderFormat) ? $creaderFormat : 1;

if (count($system_global->recommended_fixes) > 0) {
    foreach ($system_global->recommended_fixes as $fix) {
        /* @var $fix DUP_PRO_Recommended_Fix */
        if (
            in_array($fix->recommended_fix_type, array(
                DUP_PRO_Recommended_Fix_Type::Text,
                DUP_PRO_Recommended_Fix_Type::QuickFix
            ), true) !== false
        ) {
            $recommended_text_fix_present = true;
        }
    }
}

if (isset($_SERVER['AUTH_TYPE']) && $_SERVER['AUTH_TYPE'] == 'Basic' && !$global->basic_auth_enabled) {
    $recommended_text_fix_present = true;
    $system_global->add_recommended_quick_fix(
        'Set authentication username and password',
        'Automatically set basic auth username and password',
        array(
            'special' => array(
                'set_basic_auth' => 1
            )
        )
    );
}

if (isset($_GET['dpro_show_error'])) {
    $recommended_text_fix_present = true;
    // $system_global->add_recommended_text_fix('Test Error', 'Test fix recommendation');

    $system_global->add_recommended_quick_fix(
        'Activate DUP Archive',
        'TEST: Switch to <i><b>DUP</b></i> archive. Click on button to fix this!',
        array(
            'global' => array(
                'archive_build_mode' => 3
            )
        )
    );

    $system_global->add_recommended_quick_fix(
        'Activate ZIP Archive',
        'TEST: Switch to <i><b>ZIP</b></i> archive. Click on button to fix this!',
        array(
            'global' => array(
                'archive_build_mode' => 2
            )
        )
    );

    $system_global->add_recommended_quick_fix(
        'Activate SHELL Archive',
        'TEST: Switch to <i><b>Shell ZIP</b></i> archive. Click on button to fix this!',
        array(
            'global' => array(
                'archive_build_mode' => 1
            )
        )
    );
    //special:{stuck_5percent_pending_fix:1}
    $system_global->add_recommended_quick_fix(
        'Test Fix',
        'Let\'s fix something special',
        array(
            'special' => array(
                'stuck_5percent_pending_fix' => 1
            )
        )
    );
}

$max_pack_store       = isset($global->max_default_store_files) ? intval($global->max_default_store_files) : 0;

if ($orphan_display_msg) {
    ?>
    <div id='dpro-error-orphans' class="error">
        <p>
            <?php
            $orphan_msg = DUP_PRO_U::__(
                'There are currently (%1$s) orphaned package files taking up %2$s of space. ' .
                'These package files are no longer visible in the packages list below and are safe to remove.'
            ) . '<br/>';
            $orphan_msg .= DUP_PRO_U::__('Go to: Tools > General > Information > Stored Data > look for the [Delete Package Orphans] button for more details.') . '<br/>';
            $orphan_msg .= '<a href=' . self_admin_url('admin.php?page=duplicator-pro-tools&tab=diagnostics&orphanpurge=1') . '>' .
                DUP_PRO_U::__('Take me there now!') .
                '</a>';
            printf($orphan_msg, $orphan_info['count'], DUP_PRO_U::byteSize($orphan_info['size']));
            ?>
            <br />
        </p>
    </div>
<?php } ?>

<form id="form-duplicator" method="post">
    <?php wp_nonce_field('dpro_package_form_nonce'); ?>
    <?php $tplMng->render('admin_pages/packages/toolbar'); ?>

    <table class="widefat dpro-pktbl striped" aria-label="Packages List">
        <?php
        $tplMng->render('admin_pages/packages/packages_table_head');
        if ($totalElements == 0) {
            $tplMng->render('admin_pages/packages/no_elements_row');
        } else {
            DUP_PRO_Package::by_status_callback(
                array('Duplicator\\Views\\PackagesHelper', 'tablePackageRow'),
                array(),
                $per_page,
                $offset,
                '`id` DESC'
            );
        }
        $tplMng->render(
            'admin_pages/packages/packages_table_foot',
            array('totalElements' => $totalElements)
        ); ?>
    </table>
</form>

<?php if ($totalElements > $per_page) { ?>
    <form id="form-duplicator-nav" method="post">
        <?php wp_nonce_field('dpro_package_form_nonce'); ?>
        <div class="dpro-paged-nav tablenav">
            <?php if ($statusActive > 0) : ?>
                <div id="dpro-paged-progress" style="padding-right: 10px">
                    <i class="fas fa-circle-notch fa-spin fa-lg fa-fw"></i>
                    <i><?php DUP_PRO_U::esc_html_e('Paging disabled during build...'); ?></i>
                </div>
            <?php else : ?>
                <div id="dpro-paged-buttons">
                    <?php echo $pager->display_pagination($totalElements, $per_page); ?>
                </div>
            <?php endif; ?>
        </div>
    </form>
<?php } else { ?>
    <div style="float:right; padding:10px 5px">
        <?php echo $totalElements . '&nbsp;' . DUP_PRO_U::__("items"); ?>
    </div>
    <?php
}

$tplMng->render('admin_pages/packages/packages_scripts');
