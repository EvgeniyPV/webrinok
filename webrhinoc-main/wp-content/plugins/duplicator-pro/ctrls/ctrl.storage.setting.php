<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapUtil;

class DUP_PRO_CTRL_Storage_Setting
{

    const TAB_GENERAL  = 'general';
    const TAB_SSL      = 'ssl';
    const TAB_STORAGES = 'storage-types';

    /**
     * @var string nonce action name
     */
    const NONCE_ACTION = 'duppro-settings-storage-edit';

    /**
     * @var string main tab name
     */
    const MAIN_TAB = 'storage';

    /**
     * @var string form actionx
     */
    const FORM_ACTION = 'save';

    /**
     *
     * @var string current active tab
     */
    private static $currentSubTab = self::TAB_GENERAL;

    /**
     *
     * @var string current subtab url
     */
    private static $suceessMessage = '';

    /**
     * Get subtab URL for give subtab key
     *
     * @param string $subTabKey subtab key
     * @return url of subtab
     */
    public static function getSubTabURL($subTabKey)
    {
        if (is_multisite()) {
            $adminUrl = network_admin_url('admin.php');
        } else {
            $adminUrl = admin_url('admin.php');
        }

        $queryStr = http_build_query(array(
            'page'   => DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG,
            'tab'    => self::MAIN_TAB,
            'subtab' => $subTabKey
        ));

        return $adminUrl . '?' . $queryStr;
    }

    /**
     *
     * @return string
     */
    public static function getCurrentSubTab()
    {
        return self::$currentSubTab;
    }

    /**
     *
     * @return string[]
     */
    public static function getSubTabs()
    {
        return array(
            self::TAB_GENERAL  => DUP_PRO_U::__("General"),
            self::TAB_SSL      => DUP_PRO_U::__("SSL"),
            self::TAB_STORAGES => DUP_PRO_U::__("Storage Types")
        );
    }

    /**
     * main controller function
     *
     * @return void
     * @throws Exception
     */
    public static function controller()
    {

        DUP_PRO_Handler::init_error_handler();

        DUP_PRO_U::hasCapability('manage_options');

        switch (SnapUtil::filterInputRequest('subtab', FILTER_DEFAULT)) {
            case self::TAB_SSL:
                self::$currentSubTab = self::TAB_SSL;
                break;
            case self::TAB_STORAGES:
                self::$currentSubTab = self::TAB_STORAGES;
                break;
            case self::TAB_GENERAL:
            default:
                self::$currentSubTab = self::TAB_GENERAL;
                break;
        }

        self::processInput();
        self::doView();
    }

    /**
     * for processing input and save
     *
     * @return void
     * @throws Exception
     */
    private static function processInput()
    {
        //SAVE RESULTS
        if (!isset($_POST['action']) || $_POST['action'] != self::FORM_ACTION) {
            return;
        }

        DUP_PRO_U::verifyNonce($_POST['_wpnonce'], self::NONCE_ACTION);
        $global = DUP_PRO_Global_Entity::get_instance();

        switch (self::$currentSubTab) {
            case self::TAB_GENERAL:
                $global->storage_htaccess_off            = isset($_REQUEST['_storage_htaccess_off']) ? 1 : 0;
                $global->max_storage_retries             = (int) $_REQUEST['max_storage_retries'];
                break;
            case self::TAB_SSL:
                $global->ssl_useservercerts              = isset($_REQUEST['ssl_useservercerts']) ? 1 : 0;
                $global->ssl_disableverify               = isset($_REQUEST['ssl_disableverify']) ? 1 : 0;
                $global->ipv4_only                       = isset($_REQUEST['ipv4_only']) ? 1 : 0;
                break;
            case self::TAB_STORAGES:
                $global->gdrive_upload_chunksize_in_kb   = (int) $_REQUEST['gdrive_upload_chunksize_in_kb'];
                $global->dropbox_upload_chunksize_in_kb  = (int) $_REQUEST['dropbox_upload_chunksize_in_kb'];
                $global->dropbox_transfer_mode           = $_REQUEST['dropbox_transfer_mode'];
                $global->gdrive_transfer_mode            = $_REQUEST['gdrive_transfer_mode'];
                $global->s3_upload_part_size_in_kb       = (int) $_REQUEST['s3_upload_part_size_in_kb'];
                $global->onedrive_upload_chunksize_in_kb = filter_input(
                    INPUT_POST,
                    'onedrive_upload_chunksize_in_kb',
                    FILTER_VALIDATE_INT,
                    array(
                        'options' => array(
                            'default'   => DUPLICATOR_PRO_ONEDRIVE_UPLOAD_CHUNK_DEFAULT_SIZE_IN_KB,
                            'min_range' => DUPLICATOR_PRO_ONEDRIVE_UPLOAD_CHUNK_MIN_SIZE_IN_KB
                        )
                    )
                );
                break;

            default:
                throw new Exception("Unknown import type " . self::$currentSubTab . " detected.");
        }

        $action_updated = $global->save();
        if ($action_updated) {
            self::$suceessMessage = DUP_PRO_U::__("Storage Settings Saved");
        }
    }

    public static function doMessages()
    {
        if (!empty(self::$suceessMessage)) {
            DUP_PRO_UI_Notice::displayGeneralAdminNotice(self::$suceessMessage, DUP_PRO_UI_Notice::GEN_SUCCESS_NOTICE, false, 'dpro-wpnotice-box');
            self::$suceessMessage = '';
        }
    }

    /**
     * render view for storage settings
     *
     * @return void
     */
    private static function doView()
    {
        require(DUPLICATOR_PRO_PLUGIN_PATH . '/views/settings/storage/storage.php');
    }
}
