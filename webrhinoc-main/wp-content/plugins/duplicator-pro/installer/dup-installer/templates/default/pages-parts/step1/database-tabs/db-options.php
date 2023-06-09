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
<div class="hdr-sub3">General</div> 
<?php
$paramsManager->getHtmlFormParam(PrmMng::PARAM_BLOGNAME);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_USERS_MODE);
?>
<div class="hdr-sub3 margin-top-2">Database Settings</div>
<div class="help-target">
    <?php // DUPX_View_Funcs::helpIconLink('step2');  ?>
</div>
<?php
if (DUPX_Custom_Host_Manager::getInstance()->isManaged()) {
    $paramsManager->setFormNote(PrmMng::PARAM_DB_TABLE_PREFIX, 'The table prefix must be set according to the managed hosting where you install the site.');
}
$paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_TABLE_PREFIX);
