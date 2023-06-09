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

$global         = DUP_PRO_Global_Entity::get_instance();
$max_pack_store = isset($global->max_default_store_files) ? intval($global->max_default_store_files) : 0;
$toolTipContent = sprintf(
    DUP_PRO_U::esc_attr__(
        'The number of packages to keep is set at [%d]. To change this setting go to ' .
        'Duplicator Pro > Storage > Default > Max Packages and change the value, ' .
        'otherwise this note can be ignored.'
    ),
    absint($max_pack_store)
);
?>
<tfoot>
    <tr>
        <th colspan="7">
            <div class="dpro-pack-status-info">
                <?php if ($max_pack_store < $tplData['totalElements'] && $max_pack_store != 0) : ?>
                    <?php echo DUP_PRO_U::esc_html__("Note: max package retention enabled"); ?>
                    <i 
                        class="fas fa-question-circle fa-sm" 
                        data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Storage Packages"); ?>" 
                        data-tooltip="<?php echo $toolTipContent; ?>""
                    >
                    </i>
                <?php endif; ?>
            </div>
            <div style="float:right">
                <?php
                echo '<i>' . DUP_PRO_U::__("Time") . ': <span id="dpro-clock-container"></span></i>';
                ?>
            </div>
        </th>
    </tr>
</tfoot>