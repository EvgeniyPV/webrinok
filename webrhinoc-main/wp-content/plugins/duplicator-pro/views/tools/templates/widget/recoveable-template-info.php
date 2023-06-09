<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapIO;

// @var $template DUP_PRO_Package_Template_Entity
// @var $schedule DUP_PRO_Schedule_Entity|null
// @var $isList boolean

$isRecoveable = $template->isRecoveable($filteredData);
if (!$isRecoveable) {
    $templareRecoveryAlter          = new DUP_PRO_UI_Dialog();
    $templareRecoveryAlter->title   = (
        isset($schedule) ?
            __('Schedule: Recovery Status', 'duplicator-pro') :
            __('Template: Recovery Status', 'duplicator-pro')
        );
    $templareRecoveryAlter->width   = 700;
    $templareRecoveryAlter->height  = 570;
    $templareRecoveryAlter->message = SnapIO::getInclude(
        __DIR__ . '/template-filters-info.php',
        array(
            'template'     => $template,
            'schedule'     => isset($schedule) ? $schedule : null,
            'filteredData' => $filteredData
            )
    );
    $templareRecoveryAlter->initAlert();
    ?>
    <script>
        jQuery(document).ready(function ($) {
            $('#dup-template-recoveable-info-<?php echo $templareRecoveryAlter->getUniqueIdCounter(); ?>').click(function () {
    <?php $templareRecoveryAlter->showAlert(); ?>
            });
        });
    </script>
    <?php
}
?>
<span class="dup-template-recoveable-info-wrapper" >
    <?php
    if ($isRecoveable) {
        ?>
        <span class="grey" >
            <i class="fas fa-undo-alt" ></i> <?php _e('Enabled', 'duplicator-pro'); ?> 
        </span>
        <?php
    } else {
        ?>
        <span 
            id="dup-template-recoveable-info-<?php echo $templareRecoveryAlter->getUniqueIdCounter(); ?>" 
            class="dup-template-recoveable-info disabled maroon" 
        >
            <i class="fa fa-exclamation-triangle"></i> <span class="underline" ><?php _e('Disabled', 'duplicator-pro'); ?> </span>
        </span>
        <?php
    }

    if (!$isList) {
        ?>
        &nbsp;<i class="fas fa-question-circle fa-sm"
                 data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Recovery Status"); ?>" 
                 data-tooltip="<?php
                    if (!isset($schedule)) {
                        _e("The Recovery Status can be either 'Enabled' or 'Disabled'. "
                         . "An 'Enabled' status allows the templates archive to be restored through the recovery point wizard. "
                         . "A 'Disabled' status means the archive can still be used but just not ran as a valid restore point.", 'duplicator-pro');
                    } else {
                        _e("The Recovery Status can be either 'Enabled' or 'Disabled'. "
                         . "An 'Enabled' status allows the schedules archive to be restored through the recovery point wizard. "
                         . "A 'Disabled' status means the archive can still be used but just not ran as a valid restore point.", 'duplicator-pro');
                    }
                    ?>" ></i>
    <?php } ?>
</span>
