<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?>
<div class="help-target">
    <?php DUPX_View_Funcs::helpIconLink('step2'); ?>
</div> 
<div  class="dupx-opts">
    <?php
    if (DUPX_InstallerState::isRestoreBackup()) {
        dupxTplRender('parts/restore-backup-mode-notice');
    }
    ?>
    <div class="hdr-sub3">General Database Options</div>
    <?php
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_CHARSET);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_COLLATE);
    ?>
    <div class="param-wrapper" >
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_MYSQL_MODE);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_MYSQL_MODE_OPTS);
        ?>
    </div>
    <?php
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_SPLIT_CREATES);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_VIEW_CREATION);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_PROC_CREATION);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_FUNC_CREATION);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_REMOVE_DEFINER);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_SPACING);
    ?>
</div>
