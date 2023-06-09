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

if (empty($tplData['menuItemsL2'])) {
    return;
}
?>
<h2 class="nav-tab-wrapper">
    <?php
    foreach ($tplData['menuItemsL2'] as $item) {
        $id      = 'dup-submenu-l2-' . $tplData['currentLevelSlugs'][0] . '-' . $item['slug'];
        $classes = array('nav-tab', 'dup-submenu-l2');
        if ($item['active']) {
            $classes[] = 'nav-tab-active';
        }
        ?>
        <a href="<?php echo esc_url($item['link']); ?>" id="<?php echo esc_attr($id); ?>" class="<?php echo implode(' ', $classes); ?>" >
            <?php echo esc_html($item['label']); ?>
        </a>
    <?php } ?>
</h2>
