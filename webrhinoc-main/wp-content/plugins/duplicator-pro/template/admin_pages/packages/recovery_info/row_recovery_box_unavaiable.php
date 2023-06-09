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
DUP_PRO_Package_Recover::isLocalPackageRecoveable($package, $filteredData);

$tooltipContent = esc_attr__(
    'A package is not required to have a valid recovery point and in some cases is desirable. ' .
    'For example you may want to backup only your database.  In this case you can still ' .
    'run a database only install, however the ability to use the recovery point installer will be unavailable.',
    'duplicator-pro'
);
?>
<h3 class="dup-title gray margin-top-0" >
    <i class="fas fa-undo-alt fa-sm"></i> <?php _e('Recovery Point - Unavailable', 'duplicator-pro'); ?>&nbsp;
    <i 
        class="fas fa-question-circle fa-xs" 
        data-tooltip-title="<?php esc_attr_e('Recovery', 'duplicator-pro'); ?>" 
        data-tooltip="<?php echo $tooltipContent; ?>">
    </i>
</h3>

<?php $tplMng->render('parts/recovery/package_info_mini'); ?>

<hr>

<p>
    <small class="margin-bottom-1">
        <i class="fas fa-info-circle"></i>
        <?php
        _e(
            'Notice: Core WordPress items are missing from this package. ' .
            'These exclusions prevent it from becoming a recovery point. ' .
            'The items below would need to be included in order for a valid recovery point to be set on this package.',
            'duplicator-pro'
        );
        ?>
    </small>
</p>

<?php $tplMng->render('parts/recovery/exclude_data_box', array('filteredData' => $filteredData)); ?>