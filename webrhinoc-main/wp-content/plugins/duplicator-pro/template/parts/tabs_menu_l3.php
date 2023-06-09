<?php

/**
 * Duplicator page header
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

if (empty($tplData['menuItemsL3'])) {
    return;
}
?>
<div class="dup-sub-tabs">
    <?php
    foreach ($tplData['menuItemsL3'] as $item) {
        $id      = 'dup-submenu-l3-' . $tplData['currentLevelSlugs'][0] . '-' . $tplData['currentLevelSlugs'][1] . '-' . $item['slug'];
        $classes = array('dup-submenu-l3');
        ?>
        <span id="<?php echo esc_attr($id); ?>" class="dup-sub-tab-item <?php echo ($item['active'] ? 'dup-sub-tab-active' : ''); ?>" >
            <?php if ($item['active']) { ?>
                <b><?php echo esc_html($item['label']); ?></b> 
            <?php } else { ?>
                <a href="<?php echo esc_url($item['link']); ?>" class="<?php echo implode(' ', $classes); ?>" >
                    <span><?php echo esc_html($item['label']); ?></span>
                </a>
            <?php } ?>
        </span>
    <?php } ?>
</div>