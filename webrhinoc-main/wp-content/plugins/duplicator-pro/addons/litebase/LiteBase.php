<?php

/**
 * Version Lite Base functionalities
 *
 * Name: Duplicator LITE base
 * Version: 1
 * Author: Snap Creek
 * Author URI: http://snapcreek.com
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Addons\LiteBase;

class LiteBase extends \Duplicator\Core\Addons\AbstractAddonCore
{

    /**
     * Addon init
     *
     * @return void
     */
    public function init()
    {
        // TEMP CLASS TO TEST LITE VERSION
        require_once __DIR__ . '/License.php';
        // empty

        add_filter('duplicator_menu_pages', array($this, 'addGoProMenuPage'));
    }

    /**
     * Add go pro menu page
     *
     * @param AbstractMenuPageController[] $MenuPages menu pages
     *
     * @return AbstractMenuPageController[]
     */
    public function addGoProMenuPage($MenuPages)
    {
        $MenuPages[] = GoProPageController::getInstance();
        return $MenuPages;
    }

    /**
     * Plugin can be enabled
     *
     * @return boolean
     */
    public function canEnable()
    {
        return false;
    }

    /**
     * Return addon file path
     *
     * @return string
     */
    public static function getAddonFile()
    {
        return __FILE__;
    }

    /**
     * Return addon folder path
     *
     * @return string
     */
    public static function getAddonPath()
    {
        return __DIR__;
    }
}
