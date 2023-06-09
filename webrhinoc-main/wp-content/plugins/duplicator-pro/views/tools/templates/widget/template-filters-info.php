<?php

use Duplicator\Core\Views\TplMng;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

// @var $template DUP_PRO_Package_Template_Entity
// @var $schedule DUP_PRO_Schedule_Entity|null
// @var $filteredData array
?>
<div class="dup-recoveable-template-lightbox-wrapper" >
    <p class="maroon margin-top-0">
        <i class="fas fa-exclamation-triangle"></i>
        <i> 
            <?php
            if (isset($schedule)) {
                printf(
                    _x(
                        'Notice: Excluded core WordPress items have been applied to this schedules template. '
                        . 'These exclusions will prevent this schedule from creating a valid %1$srecovery point%2$s. '
                        . 'To change the recovery status visit the template below and make sure that it passes the recovery status test.',
                        '%1$s and %2$s represents the opening and closing HTML tags for an anchor or link',
                        'duplicator-pro'
                    ),
                    '<a href="' . esc_url(DUP_PRO_CTRL_recovery::getRecoverPageLink()) . '" target="_blank" >',
                    '</a>'
                );
            } else {
                printf(
                    _x(
                        'Notice: Excluded core WordPress items have been applied to this template. '
                        . 'These exclusions will prevent this template from creating a valid %1$srecovery point%2$s. '
                        . 'To change the recovery status edit the template and make sure that it passes the recovery status test.',
                        '%1$s and %2$s represents the opening and closing HTML tags for an anchor or link',
                        'duplicator-pro'
                    ),
                    '<a href="' . esc_url(DUP_PRO_CTRL_recovery::getRecoverPageLink()) . '" target="_blank" >',
                    '</a>'
                );
            }
            ?>
        </i>
    </p>

    <hr>
    <p>
        <?php if (isset($schedule)) {
            $toolTipContent = esc_attr__(
                'A Schedule is not required to have a recovery point. ' .
                'For example if a schedule is backing up only a database then the recovery ' .
                'will always be disabled and may be desirable.',
                'duplicator-pro'
            );
            ?>
            <b><?php _e("Schedule", 'duplicator-pro'); ?>:</b>  &quot;<?php echo esc_html($schedule->name); ?>&quot;
            <i class="fas fa-question-circle fa-sm" data-tooltip-title="Template Settings"
               data-tooltip="<?php echo $toolTipContent; ?>"
               aria-expanded="false">
            </i><br/>
            <b><?php _e("Template", 'duplicator-pro'); ?>:</b>  &quot;<a href="<?php echo esc_url($template->getEditUrl()); ?>" >
                <?php echo esc_html($template->name); ?>
            </a>&quot;
        <?php } else {
            $toolTipContent = esc_attr__(
                'A Template is not required to have a recovery point. ' .
                'For example if backing up only a database then the recovery will always be disabled and may be desirable.',
                'duplicator-pro'
            );
            ?>
            <b><?php _e("Template", 'duplicator-pro'); ?>:</b>  &quot;<?php echo esc_html($template->name); ?>&quot;
            <i class="fas fa-question-circle fa-sm" data-tooltip-title="Template Settings"
               data-tooltip="<?php echo $toolTipContent; ?>"
               aria-expanded="false">
            </i>
        <?php } ?>
    </p>
    <?php TplMng::getInstance()->render('parts/recovery/exclude_data_box', array('filteredData' => $filteredData)); ?>
</div>
