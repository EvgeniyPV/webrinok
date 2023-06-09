<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapOS;

$paramsManager = PrmMng::getInstance();
$archiveConfig = DUPX_ArchiveConfig::getInstance();
?>
<div class="help-target">
    <?php DUPX_View_Funcs::helpIconLink('step1'); ?>
</div>
<?php dupxTplRender('pages-parts/step1/options-tabs/engine-settings'); ?>
<div class="hdr-sub3 margin-top-2">Processing</div>  
<?php
$paramsManager->getHtmlFormParam(PrmMng::PARAM_SAFE_MODE);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_FILE_TIME);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_LOGGING);

if (!SnapOS::isWindows()) {
    ?>
    <div class="param-wrapper" >
        <?php $paramsManager->getHtmlFormParam(PrmMng::PARAM_SET_FILE_PERMS); ?>
        &nbsp;
        <?php $paramsManager->getHtmlFormParam(PrmMng::PARAM_FILE_PERMS_VALUE); ?>
    </div>
    <div class="param-wrapper" >
        <?php $paramsManager->getHtmlFormParam(PrmMng::PARAM_SET_DIR_PERMS); ?>
        &nbsp;
        <?php $paramsManager->getHtmlFormParam(PrmMng::PARAM_DIR_PERMS_VALUE); ?>
    </div>
    <?php
}
if (!$archiveConfig->exportOnlyDB) {
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_REMOVE_RENDUNDANT);
}
?>
<div class="hdr-sub3 margin-top-2">Configuration files</div>  
<?php
$paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONFIG);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_HTACCESS_CONFIG);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_OTHER_CONFIG);
