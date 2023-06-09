<?php

/**
 * Duplicator page header
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

defined("ABSPATH") or die("");

/**
 * Variables
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array $tplData
 */

require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/assets/js/javascript.php');
?>
<div class="wrap">
    <?php
    $tplMng->render('page/page_main_title');
