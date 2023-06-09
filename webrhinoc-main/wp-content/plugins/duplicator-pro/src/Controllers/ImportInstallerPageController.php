<?php

/**
 * Impost installer page controller
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Controllers;

use DUP_PRO_Package_Importer;
use DUP_PRO_U;
use Duplicator\Core\Bootstrap;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Controllers\AbstractSinglePageController;
use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Snap\SnapUtil;
use Exception;

class ImportInstallerPageController extends AbstractSinglePageController
{
    /** @var DUP_PRO_Package_Importer */
    protected static $importObj = null;
    /** @var string */
    protected static $iframeSrc = null;

    /**
     * Class constructor
     */
    protected function __construct()
    {
        $this->pageSlug     = ControllersManager::IMPORT_INSTALLER_PAGE;
        $this->pageTitle    = __('Install package', 'duplicator-pro');
        $this->capatibility = 'import';

        add_filter('duplicator_before_run_actions_' . $this->pageSlug, array($this, 'packageCheck'));
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
     * called on admin_print_styles-[page] hook
     *
     * @return void
     */
    public function pageStyles()
    {
        Bootstrap::styles();
        wp_enqueue_style('dup-pro-import');
    }

    /**
     * called on admin_print_scripts-[page] hook
     *
     * @return void
     */
    public function pageScripts()
    {
        self::dequeueAllScripts();
        Bootstrap::scripts();
        wp_enqueue_script('dup-pro-import-installer');
    }

    /**
     * dequeue all scripts except jquery and dup-pro script
     *
     * @return boolean // false if scripts can't be dequeued
     */
    public static function dequeueAllScripts()
    {

        if (!function_exists('wp_scripts')) {
            return false;
        }

        $scripts = wp_scripts();
        foreach ($scripts->registered as $handle => $script) {
            if (
                strpos($handle, 'jquery') === 0 ||
                strpos($handle, 'dup-pro') === 0
            ) {
                continue;
            }
            wp_dequeue_script($handle);
        }

        return true;
    }

    /**
     * Load import object and make a redirect if is a lite package
     *
     * @param array $currentLevelSlugs current menu page
     * @return void
     */
    public function packageCheck($currentLevelSlugs)
    {
        $archivePath     = SnapUtil::filterInputDefaultSanitizeString(INPUT_GET, 'package');
        self::$importObj = new DUP_PRO_Package_Importer($archivePath);

        self::$iframeSrc = self::$importObj->prepareToInstall();

        /** uncomment this to enable installer on new page
        if (self::$importObj->isLite()) {
            wp_redirect(self::$iframeSrc);
            die;
        }**/
    }

    /**
     * Render page content
     *
     * @param string[] $currentLevelSlugs current page menu levels slugs
     * @return void
     */
    public function renderContent($currentLevelSlugs)
    {
        $data = TplMng::getInstance()->getGlobalData();

        if ($data['actionsError']) {
            require(DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/import/import-installer-error.php');
        } else {
            $importObj = self::$importObj;
            $iframeSrc = self::$iframeSrc;
            require(DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/import/import-installer.php');
        }
    }
}
