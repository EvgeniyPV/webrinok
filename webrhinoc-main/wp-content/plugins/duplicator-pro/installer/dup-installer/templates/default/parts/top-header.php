<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Hooks\HooksMng;

$archiveConfig = DUPX_ArchiveConfig::getInstance();

/* Variables */
/* @var $paramView string */
?>
<table cellspacing="0" class="header-wizard">
    <tr>
        <td style="width:100%;">
            <div class="dupx-branding-header">
                <?php
                if (isset($archiveConfig->brand) && isset($archiveConfig->brand->logo) && !empty($archiveConfig->brand->logo)) {
                    echo $archiveConfig->brand->logo;
                } else {
                    ?>
                    <i class="fa fa-bolt fa-sm"></i> <?php echo HooksMng::getInstance()->applyFilters('dupx_main_header', 'Duplicator PRO'); ?>
                    <?php
                }
                ?>
            </div>
        </td>
        <td class="wiz-dupx-version">
            <a href="javascript:void(0)" onclick="DUPX.openServerDetails()">version:<?php echo $archiveConfig->version_dup; ?></a>
            <?php DUPX_View_Funcs::helpLockLink(); ?>
            <div style="padding: 6px 0">
                <?php if ($paramView !== 'help') { ?>
                    <?php DUPX_View_Funcs::installerLogLink(); ?><span>&nbsp;|&nbsp;</span><?php DUPX_View_Funcs::helpLink($paramView); ?>
                <?php } else { ?>
                    &nbsp;
                <?php } ?>
            </div>
        </td>
    </tr>
</table>
<?php
dupxTplRender('pages-parts/head/server-details');
