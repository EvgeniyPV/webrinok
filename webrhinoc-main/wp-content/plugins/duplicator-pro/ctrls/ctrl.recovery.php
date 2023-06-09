<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapUtil;

class DUP_PRO_CTRL_recovery
{

    const VIEW_WIDGET_NO_PACKAGE_SET = 'nop';
    const VIEW_WIDGET_NOT_VALID      = 'notvalid';
    const VIEW_WIDGET_VALID          = 'valid';

    /**
     *
     * @var bool
     */
    protected static $isError = false;

    /**
     *
     * @var string
     */
    protected static $errorMessage = '';

    public static function init()
    {
        add_action('current_screen', array(__CLASS__, 'addHelp'), 99);
    }

    /**
     * import installer controller
     *
     * @throws Exception
     */
    public static function controller()
    {
        self::doView();
    }

    /**
     *
     * @param WP_Screen $currentScreen
     * @return boolean
     */
    public static function addHelp($currentScreen)
    {
        if (!self::isRecoveryPage()) {
            return false;
        }
        $currentScreen->add_help_tab(array(
            'id'      => 'dup-pro-help-tab-recovery',
            'title'   => DUP_PRO_U::__('Recovery'),
            'content' => SnapIO::getInclude(DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/recovery/recovery-help-main.php')
        ));

        $currentScreen->add_help_tab(array(
            'id'      => 'dup-pro-help-tab-recovery-faq',
            'title'   => DUP_PRO_U::__('FAQ'),
            'content' => SnapIO::getInclude(DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/recovery/recovery-help-faq.php')
        ));

        $currentScreen->add_help_tab(array(
            'id'      => 'dup-pro-help-tab-recovery-cases',
            'title'   => DUP_PRO_U::__('Example Usage'),
            'content' => SnapIO::getInclude(DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/recovery/recovery-help-example-usage.php')
        ));

        $currentScreen->set_help_sidebar(self::getHelpSidebar());
    }

    protected static function getHelpSidebar()
    {
        ob_start();
        ?>
        <div class="dpro-screen-hlp-info"><b><?php DUP_PRO_U::esc_html_e('Resources'); ?>:</b> 
            <ul>
                <?php echo DUP_PRO_UI_Screen::getHelpSidebarBaseItems(); ?>
                <li>
                    <i class='fas fa-undo'></i> <a href='<?php echo DUPLICATOR_PRO_RECOVERY_GUIDE_URL; ?>' target='<?php echo DUPLICATOR_PRO_HELP_TARGET; ?>'>
                        <?php DUP_PRO_U::esc_html_e('Recovery Point Guide'); ?>
                    </a>
                </li>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function isRecoveryPage()
    {
        if (!DUP_PRO_CTRL_Tools::isToolPage()) {
            return false;
        }

        return filter_input(INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS) === 'recovery';
    }

    /**
     *
     * @return string
     */
    public static function getErrorMessage()
    {
        return self::$errorMessage;
    }

    /**
     * @return bool check if package is disallow from wp-config.php
     */
    public static function isDisallow()
    {
        if (defined('DUPLICATOR_PRO_DISALLOW_RECOVERY')) {
            return (bool) DUPLICATOR_PRO_DISALLOW_RECOVERY;
        } else {
            false;
        }
    }

    /**
     *
     * @return string
     */
    public static function getRecoverPageLink()
    {
        if (is_multisite()) {
            $url = network_admin_url('admin.php');
        } else {
            $url = admin_url('admin.php');
        }
        $queryStr = http_build_query(array(
            'page' => 'duplicator-pro-tools',
            'tab'  => 'recovery'
        ));

        return $url . '?' . $queryStr;
    }

    public static function actionResetRecoveryPoint()
    {
        try {
            if (file_exists(DUPLICATOR_PRO_PATH_RECOVER)) {
                SnapIO::rrmdir(DUPLICATOR_PRO_PATH_RECOVER);
            }
            DUP_PRO_Package_Recover::setRecoveablePackage(false);
        } catch (Exception $e) {
            self::$isError      = true;
            self::$errorMessage = $e->getMessage();
            return false;
        } catch (Error $e) {
            self::$isError      = true;
            self::$errorMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    public static function actionSetRecoveryPoint()
    {
        try {
            $recPackageId = SnapUtil::filterInputRequest('recovery_package', FILTER_VALIDATE_INT);
            if ($recPackageId === DUP_PRO_Package_Recover::getRecoverPackageId()) {
                return true;
            }

            if (file_exists(DUPLICATOR_PRO_PATH_RECOVER)) {
                SnapIO::rrmdir(DUPLICATOR_PRO_PATH_RECOVER);
            }

            $errorMessage = '';
            if (!DUP_PRO_Package_Recover::setRecoveablePackage($recPackageId, $errorMessage)) {
                throw new Exception("The old Recovery Point was removed but this package can't be set as the Recovery Point! " . $errorMessage);
            }
        } catch (Exception $e) {
            self::$isError      = true;
            self::$errorMessage = $e->getMessage();
            return false;
        } catch (Error $e) {
            self::$isError      = true;
            self::$errorMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    public static function renderRecoveryWidged($options = array(), $echo = true)
    {
        ob_start();

        $options = array_merge(
            array(
                'selector'   => false,
                'subtitle'   => '',
                'copyLink'   => false,
                'copyButton' => true,
                'launch'     => true,
                'download'   => false,
                'info'       => true
            ),
            (array) $options
        );

        $recoverPackage     = DUP_PRO_Package_Recover::getRecoverPackage();
        $recoverPackageId   = DUP_PRO_Package_Recover::getRecoverPackageId();
        $recoveablePackages = DUP_PRO_Package_Recover::getRecoverablesPackages();
        $selector           = $options['selector'];
        $subtitle           = $options['subtitle'];
        $displayCopyLink    = $options['copyLink'];
        $displayCopyButton  = $options['copyButton'];
        $displayLaunch      = $options['launch'];
        $displayDownload    = $options['download'];
        $displayInfo        = $options['info'];
        $importFailMessage  = '';

        if (!$recoverPackage instanceof DUP_PRO_Package_Recover) {
            $viewMode = self::VIEW_WIDGET_NO_PACKAGE_SET;
        } elseif (!$recoverPackage->isImportable($importFailMessage)) {
            $viewMode = self::VIEW_WIDGET_NOT_VALID;
        } else {
            $viewMode = self::VIEW_WIDGET_VALID;
        }

        require(DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/recovery/widget/recovery-widget.php');

        if ($echo) {
            ob_end_flush();
            return '';
        } else {
            return ob_get_clean();
        }
    }

    /**
     * parse view for import-installer
     */
    protected static function doView()
    {
        $recoverPackage     = DUP_PRO_Package_Recover::getRecoverPackage();
        $recoverPackageId   = DUP_PRO_Package_Recover::getRecoverPackageId();
        $recoveablePackages = DUP_PRO_Package_Recover::getRecoverablesPackages();

        require(DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/recovery/recovery.php');
    }
}