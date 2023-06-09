<?php

/**
 * Main page menu controller
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Controllers;

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractMenuPageController;

class MainPageController extends AbstractMenuPageController
{

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->pageSlug     = ControllersManager::MAIN_MENU_SLUG;
        $this->pageTitle    = 'Duplicator Plugin';
        $this->menuLabel    = apply_filters('duplicator_main_menu_label', 'Duplicator');
        $this->capatibility = self::getDefaultCapadibily();
        $this->iconUrl      = \DUP_PRO_Constants::ICON_SVG;
    }

    /**
     * Render page
     *
     * @return void
     */
    public function render()
    {
        // This page is empty because wordpress also renders the first secondary page which is the list of packages.
    }
}
