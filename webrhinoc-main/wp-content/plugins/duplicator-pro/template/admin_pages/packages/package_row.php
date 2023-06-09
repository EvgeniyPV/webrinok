<?php

/**
 * Duplicator package row in table packages list
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

defined("ABSPATH") or die("");

use Duplicator\Libs\Snap\SnapJson;

/**
 * Variables
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array $tplData
 * @var \DUP_PRO_Package $package
 */

global $packagesViewData;
$package        = $tplData['package'];
$global         = DUP_PRO_Global_Entity::get_instance();
$pack_dbonly    = false;
$txt_dbonly     = DUP_PRO_U::__('Database Only');
$rowClasses     = array('dpro-pkinfo');
$isRecoveable   = DUP_PRO_Package_Recover::isPackageIdRecoveable($package->ID);
$isRecoverPoint = (DUP_PRO_Package_Recover::getRecoverPackageId() === $package->ID);

if (is_object($package)) {
    $pack_name         = $package->Name;
    $pack_archive_size = $package->Archive->Size;
    $pack_namehash     = $package->NameHash;
    $pack_dbonly       = $package->Archive->ExportOnlyDB;
    $pack_format       = strtolower($package->Archive->Format);
    $brand             = (isset($package->Brand) && !empty($package->Brand) && is_string($package->Brand) ? $package->Brand : 'unknown');
} else {
    $pack_archive_size = 0;
    $pack_name         = 'unknown';
    $pack_namehash     = 'unknown';
    $brand             = 'unknown';
}

//Links
$uniqueid = $package->NameHash;

$remote_display     = $package->contains_non_default_storage();
$storage_problem    = $package->transferWasInterrupted();
$archive_exists     = ($package->get_local_package_file(DUP_PRO_Package_File_Type::Archive, true) != null);
$installer_exists   = ($package->get_local_package_file(DUP_PRO_Package_File_Type::Installer, true) != null);
$archive_exists_txt = ($archive_exists) ? '' : DUP_PRO_U::__("No local files, click for more info...");
$progress_error     = '';
$remote_style = ($remote_display && $storage_problem) ? 'remote-data-fail' : '';

if ($isRecoverPoint) {
    $rowClasses[] = 'dpro-recovery-package';
}

$archive_name              = $package->Archive->File;
$archiveDownloadInfoJson   = SnapJson::jsonEncodeEscAttr($package->getPackageFileDownloadInfo(DUP_PRO_Package_File_Type::Archive));
$installerDownloadInfoJson = SnapJson::jsonEncodeEscAttr($package->getInstallerDownloadInfo());
$installer_name            = $package->get_installer_filename();

switch ($package->Type) {
    case DUP_PRO_PackageType::MANUAL:
        $package_type_string = DUP_PRO_U::__('Manual');
        break;
    case DUP_PRO_PackageType::SCHEDULED:
        $package_type_string = DUP_PRO_U::__('Schedule');
        break;
    case DUP_PRO_PackageType::RUN_NOW:
        $lang_schedule  = DUP_PRO_U::__('Schedule');
        $lang_title     = DUP_PRO_U::__('This package was started manually from the schedules page.');
        $package_type_string = "{$lang_schedule}<span><sup>&nbsp;<i class='fas fa-cog fa-sm pointer' title='{$lang_title}'></i>&nbsp;</sup><span>";
        break;
    default:
        $package_type_string = DUP_PRO_U::__('Unknown');
        break;
}


if ($package->Status >= DUP_PRO_PackageStatus::COMPLETE) :
    ?>
    <!-- COMPLETE -->
    <tr class="<?php echo implode(' ', $rowClasses); ?>" id="duppro-packagerow-<?php echo $package->ID; ?>">
        <td class="pass">
            <input 
                name="delete_confirm" 
                type="checkbox" 
                id="<?php echo esc_attr($package->ID); ?>" 
                data-archive-name="<?php echo esc_attr($archive_name); ?>" 
                data-installer-name="<?php echo esc_attr($installer_name); ?>" />
        </td>
        <td>
            <?php echo $package_type_string; ?>
            <sup><?php echo $pack_format; ?></sup>
            <?php if ($pack_dbonly) { ?>
                <sup title="<?php echo $txt_dbonly; ?>">&nbsp;&nbsp;DB</sup>
            <?php } ?>
            <?php
            if ($isRecoveable) {
                $title = ($isRecoverPoint ? DUP_PRO_U::esc_attr__('Active Recovery Point') : DUP_PRO_U::esc_attr__('Recovery Point Capable'));
                ?>
                <sup>&nbsp;&nbsp;<i class="dup-pro-recoverable-status fas fa-undo-alt" data-tooltip="<?php echo $title; ?>"></i></sup>
            <?php } ?>
        </td>
            <?php if ($packagesViewData['display_brand'] === true && $packagesViewData['is_freelancer_plus']) : ?>
                <td class='brand-name'>
                    <?php echo $brand; ?>
                </td>
            <?php endif; ?>
        <td><?php echo DUP_PRO_Package::format_and_get_local_date_time($package->Created, $packagesViewData['package_ui_created']); ?></td>
        <td><?php echo DUP_PRO_U::byteSize($pack_archive_size); ?></td>
        <td class='pack-name'>
            <?php
            echo $pack_name;
            if ($isRecoverPoint) {
                echo ' ';
                $recoverPackage = DUP_PRO_Package_Recover::getRecoverPackage();
                require(DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/recovery/recovery-small-icon.php');
            }
            ?>
        </td>
        <td class="inst-name">
            <?php
            switch ($global->installer_name_mode) {
                case DUP_PRO_Global_Entity::INSTALLER_NAME_MODE_SIMPLE:
                    $lockIcon = 'fa-lock-open';
                    $installerToolTipTitle = sprintf(
                        DUP_PRO_U::__(
                            'Using standard installer name. To improve security, switch to hashed change in <a href="%1$s">%2$s</a>'
                        ),
                        get_admin_url(null, 'admin.php?page=duplicator-pro-settings&tab=package'),
                        DUP_PRO_U::__('Settings')
                    );
                    break;
                case DUP_PRO_Global_Entity::INSTALLER_NAME_MODE_WITH_HASH:
                default:
                    $lockIcon = 'fa-lock';
                    $installerToolTipTitle = DUP_PRO_U::__('Using more secure, hashed installer name.');
                    break;
            }
            $installerName = $package->get_inst_download_name();
            ?>
            <i class="fas <?php echo $lockIcon; ?> dpro-cursor-pointer" data-tooltip="<?php echo esc_html($installerToolTipTitle); ?>"></i>
            <input
                type="text" 
                readonly="readonly" 
                value="<?php echo esc_attr($installerName); ?>" 
                title="<?php echo esc_attr($installerName); ?>" 
                onfocus="jQuery(this).select();">
            <span data-dup-copy-value="<?php echo $installerName; ?>"><i class='far fa-copy dpro-cursor-pointer'></i></span>
        </td>
        <td class="get-btns">
            <!-- MENU DOWNLOAD -->
            <nav class="dpro-dnload-menu">
            <?php if ($archive_exists) : ?>
                <button <?php
                    DUP_PRO_UI::echoDisabled(!$archive_exists);
                    echo " title='{$archive_exists_txt}'"; ?>
                    class="dpro-dnload-menu-btn button no-select"
                    type="button"
                    aria-haspopup="true">
                    <i class="fa fa-download"></i> <?php DUP_PRO_U::esc_html_e("Download") ?>
                </button>
            <?php else : ?>
                <button 
                    <?php echo " title='{$archive_exists_txt}'"; ?> 
                    class="button disabled no-select" 
                    type="button" 
                    onclick="DupPro.Pack.DownloadNotice()">
                    <i class="fa fa-info-circle"></i> <?php DUP_PRO_U::esc_html_e("Download") ?>
                </button>
            <?php endif; ?>
                <nav class="dpro-dnload-menu-items">
                    <button
                        aria-label="<?php DUP_PRO_U::esc_html_e("Download Installer and Archive") ?>"
                        onClick="DupPro.Pack.DownloadFile(<?php echo $archiveDownloadInfoJson; ?>);
                                 setTimeout(function () {DupPro.Pack.DownloadInstaller(<?php echo $installerDownloadInfoJson; ?>);}, 700);
                                 jQuery(this).parent().hide();
                                 return false;">
                        <span title="<?php
                        if (!$archive_exists) {
                            DUP_PRO_U::esc_html_e("Download not accessible from here");
                        }
                        ?>">
                            <i class="fa <?php echo ($archive_exists && $installer_exists ? 'fa-download' : 'fa-exclamation-triangle') ?>"></i>&nbsp;
                            <?php DUP_PRO_U::esc_html_e("Both Files") ?>
                        </span>
                    </button>
                    <button
                        aria-label="<?php DUP_PRO_U::esc_html_e("Download Installer") ?>"
                        onClick="DupPro.Pack.DownloadInstaller(<?php echo $installerDownloadInfoJson; ?>); return false;">
                        <span title="<?php
                        if (!$installer_exists) {
                            DUP_PRO_U::esc_html_e("Download not accessible from here");
                        }
                        ?>">
                            <i class="fa <?php echo ($installer_exists ? 'fa-bolt' : 'fa-exclamation-triangle') ?>"></i>&nbsp;
                            <?php DUP_PRO_U::esc_html_e("Installer") ?>
                        </span>
                    </button>
                    <button
                        aria-label="<?php DUP_PRO_U::esc_html_e("Download Archive") ?>"
                        onClick="DupPro.Pack.DownloadFile(<?php echo $archiveDownloadInfoJson; ?>);
                                 jQuery(this).parent().hide();
                                 return false;">
                        <span title="<?php
                        if (!$archive_exists) {
                            DUP_PRO_U::esc_html_e("Download not accessible from here");
                        }
                        ?>">
                            <i class="<?php echo ($archive_exists ? 'far fa-file-archive' : 'fa fa-exclamation-triangle') ?>"></i>&nbsp;
                            <?php echo DUP_PRO_U::__("Archive") . " ({$pack_format})" ?>
                        </span>
                    </button>
                </nav>
            </nav>

            <!-- REMOTE STORE BUTTON -->
            <?php if ($storage_problem) : ?>
                <button
                    type="button"
                    class="dpro-store-btn button no-select dpro-btn-package-remote-problem"
                    aria-label="<?php DUP_PRO_U::esc_attr_e("Remote Storages") ?>"
                    onclick="DupPro.Pack.ShowRemote(<?php echo "$package->ID, '$package->NameHash'"; ?>);"
                    title="<?php DUP_PRO_U::esc_attr_e("Error during storage transfer.") ?>">
                    <i class="fa fa-database <?php echo ($remote_style); ?>"></i>
                </button>
            <?php elseif ($remote_display) : ?>
                <button
                    type="button"
                    aria-label="<?php DUP_PRO_U::esc_attr_e("Remote Storages") ?>"
                    class="dpro-store-btn button no-select dpro-btn-package-remote-ok"
                    onclick="DupPro.Pack.ShowRemote(<?php echo "$package->ID, '$package->Name'"; ?>);">
                    <i class="fa fa-database <?php echo ($remote_style); ?>"></i>
                </button>
            <?php else : ?>
                <span title="<?php DUP_PRO_U::esc_attr_e("No Remote Storages") ?>" class="dpro-store-btn-title">
                    <button
                        type="button"
                        aria-label="<?php DUP_PRO_U::esc_attr_e("Remote Storages") ?>" aria-disabled="true"
                        class="dpro-store-btn button no-select dpro-btn-package-remote-no-storage disabled">
                        <i class="fa fa-database <?php echo ($remote_style); ?>"></i>
                    </button>
                </span>
            <?php endif; ?>
            
         
            <!-- MENU BAR -->
            <nav class="dpro-bar-menu">
                <button
                    aria-haspopup="true"
                    type="button"
                    class="dpro-store-btn button no-select dpro-bar-menu-btn "
                    title="<?php DUP_PRO_U::esc_attr_e("More Items") ?>">
                    <i class="fa fa-bars"></i>
                </button>
                <nav class="dpro-bar-menu-items">
                    <button
                        aria-label="<?php DUP_PRO_U::esc_attr_e("Go to package details screen") ?>"
                        class="dpro-btn-package-details"
                        onClick="DupPro.Pack.OpenPackDetail(<?php echo "$package->ID"; ?>); return false;">
                        <span><i class="fa fa-archive fa-sm"></i> <?php DUP_PRO_U::esc_html_e("Details") ?></span>
                    </button>
                    <button
                        aria-label="<?php DUP_PRO_U::esc_attr_e("Go to package transfer screen") ?>"
                        class="dpro-btn-package-transfer"
                        onClick="DupPro.Pack.OpenPackTransfer(<?php echo "$package->ID"; ?>); return false;">
                        <span><i class="fa fa-exchange-alt"></i> <?php DUP_PRO_U::esc_html_e("Transfer") ?></span>
                    </button>
                    <?php $recovetBoxContent = $tplMng->render('admin_pages/packages/recovery_info/row_recovery_box', array(), false); ?>
                    <button
                        aria-label="<?php DUP_PRO_U::esc_attr_e("Set current package as recovery point") ?>"
                        class="dpro-btn-open-recovery-box" 
                        data-package-id="<?php echo $package->ID; ?>"
                        data-recovery-box="<?php echo esc_attr($recovetBoxContent); ?>">
                        <span>
                        <?php
                            echo ($isRecoveable)
                                ? '<i class="fas fa-undo-alt"></i>'
                                : '<i class="fa fa-exclamation-triangle maroon"></i>';
                                 _e("Recovery...", 'duplicator-pro') ?>
                        </span>
                    </button>
                </nav>
            </nav>
        </td>
    </tr>
    <?php
// NOT COMPLETE
else :
    if ($package->Status < DUP_PRO_PackageStatus::COPIEDPACKAGE) {
        // In the process of building
        $size      = 0;
        $tmpSearch = glob(DUPLICATOR_PRO_SSDIR_PATH_TMP . "/{$pack_namehash}_*");

        if (is_array($tmpSearch)) {
            $result = @array_map('filesize', $tmpSearch);
            $size   = array_sum($result);
        }
        $pack_archive_size = $size;
    }

    // If its in the pending cancels consider it stopped
    if (in_array($package->ID, $packagesViewData['pending_cancelled_package_ids'])) {
        $status = DUP_PRO_PackageStatus::PENDING_CANCEL;
    } else {
        $status = $package->Status;
    }

    if ($status >= 0) {
        $progress_css = 'run';
        if ($status >= 75) {
            $stop_button_text = DUP_PRO_U::__('Stop Transfer');
            $progress_html    = "<i class='fa fa-sync fa-sm fa-spin'></i>&nbsp;<span id='status-progress-{$package->ID}'>0</span>%"
            . "<span style='display:none' id='status-{$package->ID}'>{$status}</span>";
        } elseif ($status > 0) {
            $stop_button_text = DUP_PRO_U::__('Stop Build');
            $progress_html    = "<i class='fa fa-cog fa-sm fa-spin'></i>&nbsp;<span id='status-{$package->ID}'>{$status}</span>%";
        } else {
            // In a pending state
            $stop_button_text = DUP_PRO_U::__('Cancel Pending');
            $progress_html    = "<span style='display:none' id='status-{$package->ID}'>{$status}</span>";
        }
    } else {
        /** FAILURES AND CANCELLATIONS * */
        $progress_css = 'fail';

        if ($status == DUP_PRO_PackageStatus::ERROR) {
            $progress_error = '<div class="progress-error">'
            . '<i class="fa fa-exclamation-triangle fa-sm maroon"></i>&nbsp;'
            . '<a href="#" onclick="DupPro.Pack.OpenPackDetail(' . $package->ID . '); return false;"><b><u>' .
            DUP_PRO_U::__('Error Processing') . "</u></b></a></div><span style='display:none' id='status-" . $package->ID . "'>$status</span>";
        } elseif ($status == DUP_PRO_PackageStatus::BUILD_CANCELLED) {
            $progress_error = '<div class="progress-error"><b><i class="fa fa-exclamation-triangle fa-sm maroon"></i>&nbsp;'
            . DUP_PRO_U::__('Build Cancelled') . "</b></div><span style='display:none' id='status-" . $package->ID . "'>$status</span>";
        } elseif ($status == DUP_PRO_PackageStatus::PENDING_CANCEL) {
            $progress_error = '<div class="progress-error"><i class="fa fa-exclamation-triangle fa-sm"></i> '
            . DUP_PRO_U::__('Cancelling Build') . "</div><span style='display:none' id='status-"
            . $package->ID . "'>$status</span>";
        } elseif ($status == DUP_PRO_PackageStatus::REQUIREMENTS_FAILED) {
            $package_id            = $package->ID;
            $package               = DUP_PRO_Package::get_by_id($package_id);
            $package_log_store_dir = trailingslashit(dirname($package->StorePath));
            $is_txt_log_file_exist = file_exists("{$package_log_store_dir}{$package->NameHash}_log.txt");
            if ($is_txt_log_file_exist) {
                $link_log = "{$package->StoreURL}{$package->NameHash}_log.txt";
            } else { // .log is for backward compatibility
                $link_log = "{$package->StoreURL}{$package->NameHash}.log";
            }
            $progress_error = '<div class="progress-error"><a href="' . esc_url($link_log) . '" target="_blank">'
            . '<i class="fa fa-exclamation-triangle fa-sm"></i> '
            . DUP_PRO_U::__('Requirements Failed') . "</a></div>"
            . "<span style='display:none' id='status-" . $package->ID . "'>$status</span>";
        }
    }
    ?>

    <tr class="<?php echo implode(' ', $rowClasses); ?>" id="duppro-packagerow-<?php echo $package->ID; ?>">
        <?php if ($status >= DUP_PRO_PackageStatus::PRE_PROCESS) : ?>
            <td class="<?php echo $progress_css ?>"><input name="delete_confirm" type="checkbox" id="<?php echo $package->ID; ?>" /></td>
        <?php else : ?>
            <td class="<?php echo $progress_css ?>"><input name="delete_confirm" type="checkbox" id="<?php echo $package->ID; ?>" /></td>
        <?php endif; ?>
        <td><?php echo (($package->Type == DUP_PRO_PackageType::MANUAL) ? DUP_PRO_U::__('Manual') : DUP_PRO_U::__('Schedule')); ?></td>
        <td><?php echo DUP_PRO_Package::format_and_get_local_date_time($package->Created, $packagesViewData['package_ui_created']); ?></td>
        <td><?php echo $package->get_display_size(); ?></td>
        <td class='pack-name'>
            <?php echo ($pack_dbonly) ? "{$pack_name} <sup title='{$txt_dbonly}'>DB</sup>" : $pack_name; ?>
        </td>
        <td class='inst-name'></td>
        <td class="get-btns-transfer">
            <?php if ($status >= DUP_PRO_PackageStatus::STORAGE_PROCESSING) : ?>
                <button 
                    id="<?php echo "{$uniqueid}_{$global->installer_base_name}" ?>" 
                    <?php DUP_PRO_UI::echoDisabled(!$installer_exists); ?> 
                    class="button no-select" 
                    onClick="DupPro.Pack.DownloadInstaller(<?php echo $installerDownloadInfoJson; ?>); return false;"
                >
                    <i class="fa <?php echo ($installer_exists ? 'fa-bolt' : 'fa-exclamation-triangle') ?>"></i> <?php DUP_PRO_U::esc_html_e("Installer") ?>
                </button>
                <button 
                    id="<?php echo "{$uniqueid}_archive.zip" ?>" 
                    <?php DUP_PRO_UI::echoDisabled(!$archive_exists); ?> 
                    class="button no-select" 
                    onClick="location.href = '<?php echo $package->Archive->getURL(); ?>'; return false;"
                >
                    <i class="<?php echo ($archive_exists ? 'far fa-file-archive' : 'fa fa-exclamation-triangle') ?>"></i>&nbsp;
                    <?php DUP_PRO_U::esc_html_e("Archive") ?>
                </button>
            <?php else : ?>
                <?php if ($status == 0) : ?>
                    <button onClick="DupPro.Pack.StopBuild(<?php echo $package->ID; ?>);
                                        return false;" class="button button-large dpro-btn-stop">
                        <i class="fa fa-times fa-sm"></i> &nbsp; <?php echo $stop_button_text; ?>
                    </button>
                    <?php echo $progress_html; ?>
                <?php else : ?>
                    <?php echo $progress_error; ?>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
    <?php if ($status == DUP_PRO_PackageStatus::PRE_PROCESS) : ?>
        <!--   NO DISPLAY -->
    <?php elseif ($status > DUP_PRO_PackageStatus::PRE_PROCESS) : ?>
    <tr class="dpro-building">
        <td colspan="8" class="run">
            <div class="wp-filter dpro-build-msg">
                <?php if ($status < DUP_PRO_PackageStatus::STORAGE_PROCESSING) : ?>
                    <!-- BUILDING PROGRESS-->
                    <div id='dpro-progress-status-message-build'>
                        <div class='status-hdr'>
                            <?php _e('Building Package', 'duplicator-pro'); ?>&nbsp;<?php echo $progress_html; ?>
                        </div>
                        <small>
                            <?php _e('Please allow it to finish before creating another one.', 'duplicator-pro'); ?>
                        </small>
                    </div>
                <?php else : ?>
                    <!-- TRANSFER PROGRESS -->
                    <div id='dpro-progress-status-message-transfer'>
                        <div class='status-hdr'>
                            <?php _e('Transferring Package', 'duplicator-pro'); ?>&nbsp;<?php echo $progress_html; ?>
                        </div>
                        <small id="dpro-progress-status-message-transfer-msg">
                            <?php _e('Getting Transfer State...', 'duplicator-pro'); ?>
                        </small>
                    </div>
                <?php endif; ?>
                <div id="dpro-progress-bar-area">
                    <div class="dup-pro-meter-wrapper">
                        <div class="dup-pro-meter blue dup-pro-fullsize">
                            <span></span>
                        </div>
                        <span class="text"></span>
                    </div>
                </div>
                <button onClick="DupPro.Pack.StopBuild(<?php echo $package->ID; ?>); return false;" class="button button-large dpro-btn-stop">
                    <i class="fa fa-times fa-sm"></i> &nbsp; <?php echo $stop_button_text; ?>
                </button>
            </div>
        </td>
    </tr>
    <?php else : ?>
    <!--   NO DISPLAY -->
    <?php endif; ?>
<?php endif; ?>
<?php
$packagesViewData['rowCount']++;
