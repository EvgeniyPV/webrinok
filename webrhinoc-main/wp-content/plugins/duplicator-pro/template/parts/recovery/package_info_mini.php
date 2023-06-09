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
?>
<table class="_dev-rec-table">
    <tbody>
        <tr>
            <td>
                <b><?php esc_html_e('Name', 'duplicator-pro'); ?>:</b>
            </td>
            <td>
                <?php echo esc_html($package->Name); ?>
            </td>
        </tr>
        <tr>
            <td>
                <b><?php esc_html_e('Date', 'duplicator-pro'); ?>:</b>
            </td>
            <td>
                <?php echo $package->Created; ?> | <?php
                    $hours = $package->getPackageLife();
                    printf(_n('Created %d hour ago.', 'Created %d hours ago.', $hours, 'duplicator-pro'), $hours);
                ?>
            </td>
        </tr>
    </tbody>
</table>