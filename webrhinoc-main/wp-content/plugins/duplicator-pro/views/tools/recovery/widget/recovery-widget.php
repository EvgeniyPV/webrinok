<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* passed values */
/* @var $recoverPackage DUP_PRO_Package_Recover */
/* @var $recoverPackageId int */
/* @var $recoveablePackages array */
/* @var $selector bool */
/* @var $subtitle string */
/* @var $displayCopyLink bool */
/* @var $displayCopyButton bool */
/* @var $displayLaunch bool */
/* @var $displayDownload bool */
/* @var $displayInfo bool */
/* @var $viewMode string */
/* @var $importFailMessage string */
?>
<div class="dup-pro-recovery-widget-wrapper" >
    <div class="dup-pro-recovery-point-details margin-bottom-1">
        <?php require dirname(__FILE__) . '/recovery-widget-details.php'; ?>
    </div>
    <?php require dirname(__FILE__) . '/recovery-widget-selector.php'; ?>
    <div class="dup-pro-recovery-point-actions">
        <?php require dirname(__FILE__) . '/recovery-widget-link-actions.php'; ?>
    </div>
</div>
