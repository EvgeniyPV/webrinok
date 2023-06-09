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

?>
<tr class="dpro-nopackages">
    <td colspan="7" class="dpro-list-nopackages">
        <br />
        <i class="fa fa-archive fa-sm"></i>
        <?php DUP_PRO_U::esc_html_e("No Packages Found."); ?><br />
        <?php DUP_PRO_U::esc_html_e("Click the 'Create New' button to build a package."); ?>
        <div class="dpro-quick-start">
            <?php DUP_PRO_U::esc_html_e("New to Duplicator?"); ?><br />
            <a href="https://snapcreek.com/duplicator/docs/quick-start/" target="_blank">
                <?php DUP_PRO_U::esc_html_e("Check out the 'Quick Start' guide!"); ?>
            </a>
        </div>
        <div style="height:75px">&nbsp;</div>
    </td>
</tr>