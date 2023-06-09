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
    <?php DUPX_View_Funcs::helpIconLink('step3'); ?>
</div>
<div class="hdr-sub3">WP-config File Settings</div>
<div  class="dupx-opts">
    <?php
    if (DUPX_InstallerState::isRestoreBackup()) {
        dupxTplRender('parts/restore-backup-mode-notice');
    } else {
        ?>
        <p>
            See the <a href="https://wordpress.org/support/article/editing-wp-config-php/" target="_blank">WordPress documentation for more information</a>.
        </p>
        <div class="hdr-sub3">Posts/Pages</div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_DISALLOW_FILE_EDIT);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_DISALLOW_FILE_MODS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_AUTOSAVE_INTERVAL);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_POST_REVISIONS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_EMPTY_TRASH_DAYS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_IMAGE_EDIT_OVERWRITE);
        ?>
        <div class="hdr-sub3 margin-top">Security</div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_FORCE_SSL_ADMIN);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_AUTOMATIC_UPDATER_DISABLED);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_AUTO_UPDATE_CORE);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_GEN_WP_AUTH_KEY);
        ?>
        <div class="hdr-sub3 margin-top">System/General</div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_CACHE);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_DEBUG);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_DEBUG_LOG);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_DISABLE_FATAL_ERROR_HANDLER);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_DEBUG_DISPLAY);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_SCRIPT_DEBUG);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_CONCATENATE_SCRIPTS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_SAVEQUERIES);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_ALTERNATE_WP_CRON);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_DISABLE_WP_CRON);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_CRON_LOCK_TIMEOUT);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_COOKIE_DOMAIN);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_MEMORY_LIMIT);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_MAX_MEMORY_LIMIT);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_TEMP_DIR);
        ?>
        <div class="hdr-sub3 margin-top">Other Settings</div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WPCACHEHOME);
    }
    ?>
</div>