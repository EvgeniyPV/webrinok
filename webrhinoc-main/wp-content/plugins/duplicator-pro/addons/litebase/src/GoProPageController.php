<?php

/**
 * Import menu page controller
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Addons\LiteBase;

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractMenuPageController;

class GoProPageController extends AbstractMenuPageController
{
    const PAGE_SLUG = 'duplicator-gopro';

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->parentSlug   = ControllersManager::MAIN_MENU_SLUG;
        $this->pageSlug     = self::PAGE_SLUG;
        $this->pageTitle    = __('Go Pro!', 'duplicator-pro');
        $this->menuLabel    = '<span style="color:#f18500" >' . __('Go Pro!', 'duplicator-pro') . '</span>';
        $this->capatibility = self::getDefaultCapadibily();
        $this->menuPos      = 200;

        add_filter('duplicator_render_page_content_' . $this->pageSlug, array($this, 'renderContent'));
    }

    /**
     * Return true if addon is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
            return true;
    }

    /**
     * Render content page
     *
     * @param string[] $currentLevelSlugs current page/tabs
     *
     * @return void
     */
    public function renderContent($currentLevelSlugs)
    {
        echo 'GO PROOOOOOO!!!!!!!!!!!!!!!!!';
    }
}
