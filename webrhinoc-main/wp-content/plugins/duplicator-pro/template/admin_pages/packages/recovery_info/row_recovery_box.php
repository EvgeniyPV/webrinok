<?php

/**
 * Duplicator package row in table packages list
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

 /** @var \DUP_PRO_Package */
$package        = $tplData['package'];
$isRecoveable   = DUP_PRO_Package_Recover::isPackageIdRecoveable($package->ID);
$isRecoverPoint = (DUP_PRO_Package_Recover::getRecoverPackageId() === $package->ID);

if ($isRecoveable) {
    $tplMng->render('admin_pages/packages/recovery_info/row_recovery_box_avaiable');
} else {
    $tplMng->render('admin_pages/packages/recovery_info/row_recovery_box_unavaiable');
}
