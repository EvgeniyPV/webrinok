<?php

use Duplicator\Libs\DupArchive\DupArchiveEngine;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* @var $recoverPackage DUP_PRO_Package_Recover */
/* @var $recoverPackageId int */
/* @var $recoveablePackages array */
?>
<h2 class="margin-bottom-0">
    <i class="fas fa-undo-alt"></i> <?php DUP_PRO_U::esc_html_e("Recovery Point"); ?>
</h2>
<hr/>

<p class="margin-bottom-1">
    <?php DUP_PRO_U::esc_html_e("Quickly restore this site to a specific point in time."); ?>
    <span class="link-style dup-pro-open-help-link">
        <?php DUP_PRO_U::esc_html_e("Need more help?"); ?>
    </span>
</p>
<div class="dup-pro-recovery-details-max-width-wrapper" >
    <?php if (DUP_PRO_CTRL_recovery::isDisallow()) { ?>
        <p>
            <?php DUP_PRO_U::esc_html_e("The import function is disabled"); ?>
        </p>
        <?php
        return;
    }
    ?>
    <form id="dpro-recovery-form" method="post">
        <?php
        DUP_PRO_CTRL_recovery::renderRecoveryWidged(array(
            'selector'   => true,
            'subtitle'   => '',
            'copyLink'   => true,
            'copyButton' => true,
            'launch'     => true,
            'download'   => true,
            'info'       => true
        ));
        ?>
    </form>
</div>
<?php
require_once DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/recovery/widget/recovery-widget-scripts.php';
