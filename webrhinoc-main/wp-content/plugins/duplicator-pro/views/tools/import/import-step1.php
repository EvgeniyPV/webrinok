<?php

use Duplicator\Controllers\ImportInstallerPageController;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

// @var $viewMode string // single | list
// @var $adminMessageViewModeSwtich bool

if (!ImportInstallerPageController::getInstance()->isEnabled()) {
    ?>
    <div class="dup-pro-import-header" >
        <h2 class="title">
            <i class="fas fa-arrow-alt-circle-down"></i> <?php DUP_PRO_U::esc_html_e("Import"); ?>
        </h2>
        <hr />
    </div>
    <p>
        <?php DUP_PRO_U::esc_html_e("The import function is disabled"); ?>
    </p>
    <?php
    return;
}

switch ($viewMode) {
    case DUP_PRO_CTRL_import::VIEW_MODE_ADVANCED:
        $viewModeClass = 'view-list-item';
        break;
    case DUP_PRO_CTRL_import::VIEW_MODE_BASIC:
    default:
        $viewModeClass = 'view-single-item';
        break;
}

if ($adminMessageViewModeSwtich) {
    require dirname(__FILE__) . '/import-message-view-mode-switch.php';
}

if (DUP_PRO_Global_Entity::get_instance()->import_chunk_size == 0) {
    $footerChunkInfo = sprintf(DUP_PRO_U::__('<b>Chunk Size:</b> N/A &nbsp;|&nbsp; <b>Max Size:</b> %s'), size_format(wp_max_upload_size()));
    $toolTipContent  = DUP_PRO_U::__('If you need to upload a larger file, go to [Settings > Import] and set Upload Chunk Size');
} else {
    $footerChunkInfo = sprintf(DUP_PRO_U::__('<b>Chunk Size:</b> %s &nbsp;|&nbsp; <b>Max Size:</b> No Limit'), size_format(DUP_PRO_CTRL_import::getChunkSize() * 1024));
    $toolTipContent  = DUP_PRO_U::__('The max file size limit is ignored when chunk size is enabled.  '
            . 'Use a large chunk size with fast connections and a small size with slower connections.  '
            . 'You can change the chunk size by going to [Settings > Import].');
}

$hlpUpload  = DUP_PRO_U::__('Upload speeds can be affected by various server connections and setups.  Additionally, chunk size can influence the '
    . 'upload speed [Settings > Import].  If changing the chunk size is still slow, try uploading the archive manually with these steps:');

$hlpUpload .= '<ul>' .
    '<li>' . DUP_PRO_U::__('1. Cancel current upload') . '</li>' .
    '<li>' . DUP_PRO_U::__('2. Manually upload archive to:<br/> &nbsp; &nbsp; <i>/wp-content/backups-dup-pro/imports/</i>') . '</li>' .
    '<li>' . DUP_PRO_U::__('3. Refresh the Import screen') . '</li>' .
    '</ul>';
?>

<div class="dup-pro-import-header" >
    <h2 class="title">
        <i class="fas fa-arrow-alt-circle-down"></i> <?php printf(DUP_PRO_U::esc_html__("Step %s of 2: Upload Archive"), '<span class="red">1</span>'); ?>
    </h2>
    <div class="options" >
        <?php require dirname(__FILE__) . '/import-views-and-options.php'; ?>
    </div>
    <hr />
</div>

<!-- ==============================
DRAG/DROP AREA -->
<?php $packRowTemplate   = dirname(__FILE__) . '/import-package-row.php'; ?>
<div id="dup-pro-import-upload-file" ></div>
<div class="no_display" >
    <div id="dup-pro-import-upload-file-content">
        <i class="fa fa-download fa-2x"></i>
        <div class="dup-drag-drop-message">
            <?php DUP_PRO_U::esc_html_e("Drag & Drop Archive File Here"); ?>
            <input id="dup-import-dd-btn" type="button" class="button button-large button-default" name="dpro-files" value="<?php DUP_PRO_U::esc_attr_e("Select File..."); ?>">
        </div>
    </div>
</div>
<div id="dup-pro-import-upload-file-footer">
    <i class="fas fa-question-circle fa-sm" data-tooltip-title="<?php DUP_PRO_U::esc_html_e("Upload Chunk Size"); ?>" data-tooltip="<?php echo esc_attr($toolTipContent); ?>"></i> <?php echo $footerChunkInfo; ?> &nbsp;|&nbsp;
    <span class="pointer link-style" data-tooltip-title="<?php DUP_PRO_U::esc_html_e("Improve Upload Speed"); ?>" data-tooltip="<?php echo esc_attr($hlpUpload); ?>" >
        <i><?php DUP_PRO_U::esc_html_e('Slow Upload'); ?></i> <i class="fas fa-question-circle fa-sm" ></i>
    </span>
 </div>
<br/><br/>

<!-- ==============================
PACKAGE DETAILS: Basic/Advanced -->
<div id="dpro-pro-import-available-packages" class="<?php echo $viewModeClass; ?>" >
    <table class="dup-import-avail-packs packages-list">
        <thead>
            <tr>
                <th class="name"><?php DUP_PRO_U::esc_html_e("Archives"); ?></th>
                <th class="size"><?php DUP_PRO_U::esc_html_e("Size"); ?></th>
                <th class="created"><?php DUP_PRO_U::esc_html_e("Created"); ?></th>
                <th class="funcs"><?php DUP_PRO_U::esc_html_e("Status"); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $importObjs = DUP_PRO_Package_Importer::getArchiveObjects();
            if (count($importObjs) === 0) {
                require dirname(__FILE__) . '/import-package-row-no-found.php';
            } else {
                foreach ($importObjs as $importObj) {
                    require $packRowTemplate;
                }
                $importObj = null;
            }
            ?>
        </tbody>
    </table>
    <div class="no_display" >
        <table id="dup-pro-import-available-packages-templates">
            <?php
            $idRow = 'dup-pro-import-row-template';
            require $packRowTemplate;
            require dirname(__FILE__) . '/import-package-row-no-found.php';
            ?>
        </table>
    </div>
</div>


