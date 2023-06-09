<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $importObj DUP_PRO_Package_Importer */
/* @var $iframeSrc string */

if (!$importObj->isImportable($importFailMessage)) {
    ?>
    <div class="wrap">
        <h1>
            <?php _e("Install package", 'duplicator-pro'); ?>
        </h1>
        <div class="dpro-pro-import-installer-content-wrapper" >
            <p class="orangered">
                <?php echo esc_html($importFailMessage); ?>
            </p>
        </div>
    </div>
<?php } else { ?>
    <div id="dpro-pro-import-installer-wrapper"  >
        <div id="dpro-pro-import-installer-top-bar" class="dup-pro-recovery-details-max-width-wrapper" >
            <a href="<?php echo esc_url(DUP_PRO_CTRL_import::getImportPageLink()); ?>" class="button" >
                <i class="fa fa-caret-left"></i> <?php echo DUP_PRO_U::__("Back to Import"); ?>
            </a>
            <span class="link-style no-decoration recovery-copy-top-wrapper" >
                <?php if (($recoverPackage = DUP_PRO_Package_Recover::getRecoverPackage()) !== false) { ?>
                    <span class="button" 
                          data-dup-copy-value="<?php echo $recoverPackage->getInstallLink(); ?>"
                          data-dup-copy-title="<?php DUP_PRO_U::_e("Copy Recovery URL to clipboard"); ?>"
                          data-dup-copied-title="<?php DUP_PRO_U::_e("Recovery URL copied to clipboard"); ?>" >
                              <?php DUP_PRO_U::_e("Copy Recovery URL"); ?>
                    </span>
                <?php } else { ?>
                    <span class="button disabled"><i class="fas fa-exclamation-circle"></i> <?php DUP_PRO_U::_e("Recovery Point Not Set"); ?></span>
                <?php } ?>
            </span>
        </div>
        <div id="dup-pro-import-installer-modal" class="no-display"></div>
        <iframe id="dpro-pro-import-installer-iframe" src="<?php echo esc_url($iframeSrc); ?>" ></iframe>
    </div>
    <?php
}