<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager     = PrmMng::getInstance();
$isAdvancedConfirm = DUPX_InstallerState::isAddSiteOnMultisite();
$recoveryLink      = PrmMng::getInstance()->getValue(PrmMng::PARAM_RECOVERY_LINK);
$checkAdvLabel     = empty($recoveryLink) ? 'Are you sure you want to proceed without a Recovery Point?' : 'I confirm that have a copy of the Recovery URL';

?>
<div id="db-install-dialog-confirm" title="Install Confirmation" style="display:none">
    <p>
        <b>Run installer with these settings?</b>
    </p>
    <p>
        <b>Install type:</b> <?php echo DUPX_InstallerState::installTypeToString(); ?>
    </p>

    <b>Site settings:</b>
    <?php if (DUPX_InstallerState::isAddSiteOnMultisite()) {
            /** @var SiteOwrMap[] $overwriteMapping */
            $overwriteMapping = PrmMng::getInstance()->getValue(PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING);
        ?>
        <ul class="margin-bottom-1" >
        <?php foreach ($overwriteMapping as $map) {
            $sourceInfo = $map->getSourceSiteInfo();
            ?>
            <li>
            <?php
            if ($map->getTargetId() == 0) { ?>
                Install site <b><?php echo $sourceInfo['fullHomeUrl']; ?></b> on new site
            <?php  } else {
                $targetInfo = $map->getTargetSiteInfo();
                ?>
                <i class="fas fa-exclamation-triangle maroon"></i> 
                Overwrite site <b><?php echo $targetInfo['fullHomeUrl']; ?></b> with <b><?php echo $sourceInfo['fullHomeUrl']; ?></b>
            <?php } ?>
            </li>
        <?php } ?>
        </ul>
    <?php } else { ?>
        <table class="margin-bottom-1 margin-left-1" >
            <tr>
                <td><b>New URL:</b></td>
                <td><i id="dlg-url-new"><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_URL_NEW)); ?></i></td>
            </tr>
            <tr>
                <td><b>New Path:</b></td>
                <td><i id="dlg-path-new"><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_PATH_NEW)); ?></i></td>
            </tr>
        </table> 
    <?php } ?>

    <b>Database Settings:</b><br/>
    <table class="margin-bottom-1 margin-left-1" >
        <tr>
            <td><b>Server:</b></td>
            <td><i id="dlg-dbhost"><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_DB_HOST)); ?></i></td>
        </tr>
        <tr>
            <td><b>Name:</b></td>
            <td><i id="dlg-dbname"><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_DB_NAME)); ?></i></td>
        </tr>
        <tr>
            <td><b>User:</b></td>
            <td><i id="dlg-dbuser"><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_DB_USER)); ?></i></td>
        </tr>
    </table>

    <small class="maroon" >
        <i class="fa fa-exclamation-triangle"></i> 
        WARNING: Be sure these database parameters are correct! Entering the wrong information WILL overwrite an existing database.
        Make sure to have backups of all your data before proceeding.
    </small>

    <div class="advanced-confirm <?php echo ($isAdvancedConfirm ? '' : 'no-display'); ?>">
        <hr class="separator" >
        <div class="maroon" >
            <b>Multisite Subsite Validation:</b><br/>
            <label>
                <input type="checkbox" id="dialog-adv-confirm-check" > <?php echo $checkAdvLabel; ?> 
            </label>
            <?php if (!empty($recoveryLink)) { ?>
                <span class="copy-link secondary-btn"  
                    data-dup-copy-value="<?php echo DUPX_U::esc_url($recoveryLink); ?>"
                    data-dup-copy-title="<?php echo DUPX_U::esc_attr("Copy Recovery URL to clipboard"); ?>"
                    data-dup-copied-title="<?php echo DUPX_U::esc_attr("Recovery URL copied to clipboard"); ?>" >
                    Copy
                    <i class="far fa-copy copy-icon"></i>
                </span>
            <?php } ?>
            <br>

            <?php if (empty($recoveryLink)) { ?>
                <small>
                    This is a delicate operation and if there is a problem you won't be able to recover your site!
                </small>
            <?php } else { ?>
                <small>
                    You are about to proceed with a delicate operation. 
                    Be sure to copy and paste the recovery point URL to a safe spot so you can recover the original site should a problem occur.
                </small>
            <?php } ?>
        </div>
    </div>
</div>