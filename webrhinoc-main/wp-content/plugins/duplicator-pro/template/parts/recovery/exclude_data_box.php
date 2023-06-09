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

$filteredData = $tplData['filteredData'];
?>
<fieldset class="dup-template-filtered-info-box">
    <legend><b><?php _e("Excluded Data", 'duplicator-pro'); ?></b></legend>

    <?php if ($filteredData['dbonly']) { ?>
        <p class="margin-top-0 maroon" >
            <b><?php _e("This template is a database only", 'duplicator-pro'); ?></b>
        </p>
        <?php
    }
    ?>
    <h5 class="margin-top-0 margin-bottom-0"><?php _e("CORE FOLDERS", 'duplicator-pro'); ?>:</h5>
    <?php if (count($filteredData['filterDirs']) > 0) { ?>
        <ul class="margin-top-0">
            <?php foreach ($filteredData['filterDirs'] as $path) { ?>
                <li><small><i><?php echo esc_html($path); ?></i></small></li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p class="margin-top-0" >
            <?php _e("no filters set", 'duplicator-pro'); ?>
        </p>
    <?php } ?>
    <h5 class="margin-top-0 margin-bottom-0"><?php _e("TABLES", 'duplicator-pro'); ?>:</h5>
    <?php if (count($filteredData['filterTables']) > 0) { ?>
        <ul class="margin-top-0">
            <?php foreach ($filteredData['filterTables'] as $table) { ?>
                <li><small><i><?php echo esc_html($table); ?></i></small></li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p class="margin-top-0" >
            <?php _e("no filters set", 'duplicator-pro'); ?>
        </p>
    <?php } ?>
</fieldset>