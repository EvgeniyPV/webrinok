<?php

/**
 * Tools page controller
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Controllers;

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractMenuPageController;

class ToolsPageController extends AbstractMenuPageController
{
    const NONCE_ACTION = 'duppro-settings-package';

    /**
     * tabs menu
     */
    const L2_SLUG_DISAGNOSTIC = 'diagnostics';
    const L2_SLUG_TEMPLATE    = 'templates';
    const L2_SLUG_RECOVERY    = 'recovery';

    /**
     * disagnostic
     */
    const L3_SLUG_DISAGNOSTIC_DIAGNOSTIC = 'diagnosticsdiagnostic';
    const L3_SLUG_DISAGNOSTIC_LOG        = 'log';
    const L3_SLUG_DISAGNOSTIC_PHPLOGS    = 'phplogs';
    const L3_SLUG_DISAGNOSTIC_SUPPORT    = 'support';

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->parentSlug   = ControllersManager::MAIN_MENU_SLUG;
        $this->pageSlug     = ControllersManager::TOOLS_SUBMENU_SLUG;
        $this->pageTitle    = __('Tools', 'duplicator-pro');
        $this->menuLabel    = __('Tools', 'duplicator-pro');
        $this->capatibility = self::getDefaultCapadibily();
        $this->menuPos      = 50;

        add_filter('duplicator_sub_menu_items_' . $this->pageSlug, array($this, 'getBasicSubMenus'));
        add_filter('duplicator_sub_level_default_tab_' . $this->pageSlug, array($this, 'getSubMenuDefaults'), 10, 2);
        add_filter('duplicator_render_page_content_' . $this->pageSlug, array($this, 'renderContent'));
    }

    /**
     * Return sub menus for current page
     *
     * @param array $subMenus sub menus list
     *
     * @return array
     */
    public function getBasicSubMenus($subMenus)
    {
        $subMenus[] = self::generateSubMenuItem(self::L2_SLUG_DISAGNOSTIC, __('General', 'duplicator-pro'));
        $subMenus[] = self::generateSubMenuItem(self::L2_SLUG_TEMPLATE, __('Templates', 'duplicator-pro'));
        $subMenus[] = self::generateSubMenuItem(self::L2_SLUG_RECOVERY, __('Recovery', 'duplicator-pro'));

        $subMenus[] = self::generateSubMenuItem(self::L3_SLUG_DISAGNOSTIC_DIAGNOSTIC, __('Information', 'duplicator-pro'), self::L2_SLUG_DISAGNOSTIC);
        $subMenus[] = self::generateSubMenuItem(self::L3_SLUG_DISAGNOSTIC_LOG, __('Duplicator Logs', 'duplicator-pro'), self::L2_SLUG_DISAGNOSTIC);
        $subMenus[] = self::generateSubMenuItem(self::L3_SLUG_DISAGNOSTIC_PHPLOGS, __('PHP Logs', 'duplicator-pro'), self::L2_SLUG_DISAGNOSTIC);
        $subMenus[] = self::generateSubMenuItem(self::L3_SLUG_DISAGNOSTIC_SUPPORT, __('Support', 'duplicator-pro'), self::L2_SLUG_DISAGNOSTIC);

        return $subMenus;
    }

    /**
     * Return slug default for parent menu slug
     *
     * @param string $slug   current default
     * @param string $parent parent for default
     *
     * @return string default slug
     */
    public function getSubMenuDefaults($slug, $parent)
    {
        switch ($parent) {
            case '':
                return self::L2_SLUG_DISAGNOSTIC;
            case self::L2_SLUG_DISAGNOSTIC:
                return self::L3_SLUG_DISAGNOSTIC_DIAGNOSTIC;
            default:
                return $slug;
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
        switch ($currentLevelSlugs[1]) {
            case self::L2_SLUG_DISAGNOSTIC:
                $this->renderDiagnostic($currentLevelSlugs);
                break;
            case self::L2_SLUG_TEMPLATE:
                require(DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/templates/main.php');
                break;
            case self::L2_SLUG_RECOVERY:
                \DUP_PRO_CTRL_recovery::controller();
                break;
        }
    }

    /**
     * Render diagnostic sub tab
     *
     * @param string[] $currentLevelSlugs current page menu levels slugs
     *
     * @return void
     */
    protected function renderDiagnostic($currentLevelSlugs)
    {
        require DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/diagnostics/main.php';

        switch ($currentLevelSlugs[2]) {
            case self::L3_SLUG_DISAGNOSTIC_DIAGNOSTIC:
                require DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/diagnostics/diagnostic.php';
                break;
            case self::L3_SLUG_DISAGNOSTIC_LOG:
                require DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/diagnostics/log.php';
                break;
            case self::L3_SLUG_DISAGNOSTIC_PHPLOGS:
                require DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/diagnostics/phplogs.php';
                break;
            case self::L3_SLUG_DISAGNOSTIC_SUPPORT:
                require DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/diagnostics/support.php';
                break;
        }
    }

    /**
     * Return clean installer files action URL
     *
     * @return string
     */
    public static function getCleanFilesAcrtionUrl()
    {
        return ControllersManager::getMenuLink(
            ControllersManager::TOOLS_SUBMENU_SLUG,
            self::L2_SLUG_DISAGNOSTIC,
            self::L3_SLUG_DISAGNOSTIC_DIAGNOSTIC,
            array(
                    'action' => 'installer'
                )
        );
    }

    /**
     * Return remove cache action URL
     *
     * @return string
     */
    public static function getRemoveCacheActionUrl()
    {
        return ControllersManager::getMenuLink(
            ControllersManager::TOOLS_SUBMENU_SLUG,
            self::L2_SLUG_DISAGNOSTIC,
            self::L3_SLUG_DISAGNOSTIC_DIAGNOSTIC,
            array(
                    'action' => 'tmp-cache'
                )
        );
    }

    /**
     * Return purge orphan packages action URL
     *
     * @return string
     */
    public static function getPurgeOrphanActionUrl()
    {
        return ControllersManager::getMenuLink(
            ControllersManager::TOOLS_SUBMENU_SLUG,
            self::L2_SLUG_DISAGNOSTIC,
            self::L3_SLUG_DISAGNOSTIC_DIAGNOSTIC,
            array(
                    'action' => 'purge-orphans'
                )
        );
    }
}
