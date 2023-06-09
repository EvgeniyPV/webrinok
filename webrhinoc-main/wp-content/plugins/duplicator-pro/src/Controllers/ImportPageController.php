<?php

/**
 * Import menu page controller
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Controllers;

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractMenuPageController;

class ImportPageController extends AbstractMenuPageController
{

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->parentSlug   = ControllersManager::MAIN_MENU_SLUG;
        $this->pageSlug     = ControllersManager::IMPORT_SUBMENU_SLUG;
        $this->pageTitle    = __('Import', 'duplicator-pro');
        $this->menuLabel    = __('Import', 'duplicator-pro');
        $this->capatibility = 'import';
        $this->menuPos      = 20;

        add_filter('duplicator_render_page_content_' . $this->pageSlug, array($this, 'renderContent'));
    }

    /**
     * Return true if current page is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        if (defined('DUPLICATOR_PRO_DISALLOW_IMPORT')) {
            return !((bool) DUPLICATOR_PRO_DISALLOW_IMPORT);
        } else {
            return true;
        }
    }

    /**
     * Render page content
     *
     * @param string[] $currentLevelSlugs current page menu levels slugs
     * @return void
     */
    public function renderContent($currentLevelSlugs)
    {
        \DUP_PRO_CTRL_import::controller();
    }
}
