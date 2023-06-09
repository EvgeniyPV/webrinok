<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* passed values */
/* @var $importObj DUP_PRO_Package_Importer */

if (!$importObj instanceof DUP_PRO_Package_Importer) {
    return;
}
?>
<div class="dup-pro-import-package-detail-content">
    <?php
    if (!$importObj->isImportable($importFailMessage)) {
        ?>
        <p class="maroon">
            <b><i class="fas fa-exclamation-triangle"></i> <?php echo $importFailMessage; ?></b>
        </p>
        <?php
    } elseif ($importObj->haveImportWaring($importWarnMessage)) {
        ?>
        <p class="gray">
            <b><i class="fas fa-exclamation-triangle"></i> <?php echo $importWarnMessage; ?></b>
        </p>
        <?php
    } else {
        ?>
        <p class="green">
            <b><i class="fas fa-check-circle"></i> <?php DUP_PRO_U::_e('This package is ready to install, click the continue button to proceed.'); ?></b>
        </p>
        <?php
    }

    if ($importObj->isValid()) {
        ?>
        <ul>
            <li>
                <span class="label title"><?php DUP_PRO_U::_e('Site Details:'); ?></span>
            </li>
            <li>
                <span class="label"><?php DUP_PRO_U::_e('URL'); ?>:</span>
                <span class="value"><?php echo esc_html($importObj->getHomeUrl()); ?></span>
            </li>
            <li>
                <span class="label"><?php DUP_PRO_U::_e('Path'); ?>:</span>
                <span class="value"><?php echo esc_html($importObj->getHomePath()); ?></span>
            </li>
            <li>
                <span class="label title"><?php DUP_PRO_U::_e('Versions:'); ?></span>
            </li>
            <li>
                <span class="label"><?php DUP_PRO_U::_e('Duplicator'); ?>:</span>
                <span class="value"><?php echo esc_html($importObj->getDupVersion()); ?></span>
            </li>
            <li>
                <span class="label"><?php DUP_PRO_U::_e('Wordpress'); ?>:</span>
                <span class="value"><?php echo esc_html($importObj->getWPVersion()); ?></span>
            </li>
            <li>
                <span class="label"><?php DUP_PRO_U::_e('PHP'); ?>:</span>
                <span class="value"><?php echo esc_html($importObj->getPhpVersion()); ?></span>
            </li>
            <li>
                <span class="label title"><?php DUP_PRO_U::_e('Archive:'); ?></span>
            </li>
            <li>
                <span class="label"><?php DUP_PRO_U::_e('Created'); ?>:</span>
                <span class="value"><?php echo esc_html($importObj->getCreated()); ?></span>
            </li>
            <li>
                <span class="label"><?php DUP_PRO_U::_e('Size'); ?>:</span>
                <span class="value"><?php echo esc_html(DUP_PRO_U::byteSize($importObj->getSize())); ?></span>
            </li>
            <?php if (!$importObj->isLite()) { ?>
            <li>
                <span class="label"><?php DUP_PRO_U::_e('Folders'); ?>:</span>
                <span class="value"><?php echo esc_html(number_format($importObj->getNumFolders())); ?></span>
            </li>
            <li>
                <span class="label"><?php DUP_PRO_U::_e('Files'); ?>:</span>
                <span class="value"><?php echo esc_html(number_format($importObj->getNumFiles())); ?></span>
            </li>
            <?php } ?>
            <li>
                <span class="label title"><?php DUP_PRO_U::_e('Database:'); ?></span>
            </li>
            <li>
                <span class="label"><?php DUP_PRO_U::_e('Size'); ?>:</span>
                <span class="value"><?php echo esc_html($importObj->getDbSize()); ?></span>
            </li>
            <li>
                <span class="label"><?php DUP_PRO_U::_e('Tables'); ?>:</span>
                <span class="value"><?php echo esc_html($importObj->getNumTables()); ?></span>
            </li>
            <li>
                <span class="label"><?php DUP_PRO_U::_e('Rows'); ?>:</span>
                <span class="value"><?php echo esc_html(number_format($importObj->getNumRows())); ?></span>
            </li>
        </ul>
    <?php } ?>
</div>
