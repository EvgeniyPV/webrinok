<?php

defined("ABSPATH") or die("");

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Controllers\ToolsPageController;
use Duplicator\Libs\Snap\SnapUtil;

class DUP_PRO_Web_Services_recovery extends DUP_PRO_Web_Services
{

    public function init()
    {
        $this->add_class_action('wp_ajax_duplicator_pro_get_recovery_widget', 'get_widget');
        $this->add_class_action('wp_ajax_duplicator_pro_set_recovery', 'set_recovery');
        $this->add_class_action('wp_ajax_duplicator_pro_reset_recovery', 'reset_recovery');
    }

    protected static function getRecoveryDetailsOptions($fromPageTab)
    {
        if ($fromPageTab == ControllersManager::getPageUniqueId(ControllersManager::TOOLS_SUBMENU_SLUG, ToolsPageController::L2_SLUG_RECOVERY)) {
            $detailsOptions = array(
                'selector'   => true,
                'copyLink'   => true,
                'copyButton' => true,
                'launch'     => true,
                'download'   => false,
                'info'       => true
            );
        } elseif ($fromPageTab == ControllersManager::getPageUniqueId(ControllersManager::IMPORT_SUBMENU_SLUG)) {
            $detailsOptions = array(
                'selector'   => true,
                'launch'     => false,
                'download'   => true,
                'copyLink'   => true,
                'copyButton' => true,
                'info'       => true
            );
        } else {
            $detailsOptions = array();
        }

        return $detailsOptions;
    }

    public static function set_recovery_callback()
    {
        if (DUP_PRO_CTRL_recovery::actionSetRecoveryPoint() === false) {
            throw new Exception(DUP_PRO_CTRL_recovery::getErrorMessage());
        }

        $recoverPackage = DUP_PRO_Package_Recover::getRecoverPackage();
        if (!$recoverPackage instanceof DUP_PRO_Package_Recover) {
            throw new Exception(DUP_PRO_U::esc_html__('Can\'t get recover package'));
        }
        $fromPageTab    = SnapUtil::filterInputDefaultSanitizeString(INPUT_POST, 'fromPageTab', false);
        $detailsOptions = self::getRecoveryDetailsOptions($fromPageTab);

        if ($fromPageTab == ControllersManager::getPageUniqueId(ControllersManager::TOOLS_SUBMENU_SLUG, ToolsPageController::L2_SLUG_RECOVERY)) {
            $subtitle = DUP_PRO_U::__('Copy the link and keep it in case of need.');
        } elseif ($fromPageTab == ControllersManager::getPageUniqueId(ControllersManager::IMPORT_SUBMENU_SLUG)) {
            $subtitle = DUP_PRO_U::__('Copy the link and keep it in case of need.');
        } else {
            $subtitle = DUP_PRO_U::__('Copy the recovery URL link by clicking the recover icon <i class="fas fa-undo-alt"></i> and keep it in a safe place.<br/>');
            $subtitle .= ' ' . sprintf(DUP_PRO_U::__('For full details see <a href="%s">[Recovery Point]</a> settings.'), esc_url(DUP_PRO_CTRL_recovery::getRecoverPageLink()));
        }

        $result = array(
            'id'             => $recoverPackage->getPackageId(),
            'name'           => $recoverPackage->getPackageName(),
            'recoveryLink'   => $recoverPackage->getInstallLink(),
            'adminMessage'   => DUP_PRO_CTRL_recovery::renderRecoveryWidged(array(
                'selector'   => false,
                'subtitle'   => $subtitle,
                'copyLink'   => false,
                'copyButton' => false,
                'launch'     => false,
                'download'   => false,
                'info'       => false
                ), false),
            'packageDetails' => DUP_PRO_CTRL_recovery::renderRecoveryWidged($detailsOptions, false)
        );

        return $result;
    }

    public function set_recovery()
    {
        self::ajax_json_wrapper(array(__CLASS__, 'set_recovery_callback'), 'duplicator_pro_set_recovery', $_POST['nonce'], 'export');
    }

    public static function get_widget_callback()
    {
        $fromPageTab    = SnapUtil::filterInputDefaultSanitizeString(INPUT_POST, 'fromPageTab', false);
        $detailsOptions = self::getRecoveryDetailsOptions($fromPageTab);

        return array(
            'widget' => DUP_PRO_CTRL_recovery::renderRecoveryWidged($detailsOptions, false)
        );
    }

    public function get_widget()
    {
        self::ajax_json_wrapper(array(__CLASS__, 'get_widget_callback'), 'duplicator_pro_get_recovery_widget', $_POST['nonce'], 'export');
    }

    public static function reset_recovery_callback()
    {
        if (DUP_PRO_CTRL_recovery::actionResetRecoveryPoint() === false) {
            throw new Exception(DUP_PRO_CTRL_recovery::getErrorMessage());
        }

        $fromPageTab    = SnapUtil::filterInputDefaultSanitizeString(INPUT_POST, 'fromPageTab', false);
        $detailsOptions = self::getRecoveryDetailsOptions($fromPageTab);

        $result = array(
            'adminMessage'   => DUP_PRO_CTRL_recovery::renderRecoveryWidged(array(), false),
            'packageDetails' => DUP_PRO_CTRL_recovery::renderRecoveryWidged($detailsOptions, false)
        );

        return $result;
    }

    public function reset_recovery()
    {
        self::ajax_json_wrapper(array(__CLASS__, 'reset_recovery_callback'), 'duplicator_pro_reset_recovery', $_POST['nonce'], 'export');
    }
}
