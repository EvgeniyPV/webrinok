<?php

defined("ABSPATH") or die("");

use Duplicator\Libs\Snap\SnapUtil;

require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/ctrls/ctrl.base.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/class.scan.check.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/utilities/class.u.json.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/package/class.pack.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/entities/class.global.entity.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/entities/class.package.template.entity.php');
/**
 * Controller for Tools
 */
class DUP_PRO_CTRL_Package extends DUP_PRO_CTRL_Base
{

    /**
     *  Init this instance of the object
     */
    function __construct()
    {
        add_action('wp_ajax_DUP_PRO_CTRL_Package_addQuickFilters', array($this, 'addQuickFilters'));
        add_action('wp_ajax_DUP_PRO_CTRL_Package_switchDupArchiveNotice', array($this, 'switchDupArchiveNotice'));
        add_action('wp_ajax_DUP_PRO_CTRL_Package_toggleGiftFeatureButton', array($this, 'toggleGiftFeatureButton'));
    }

    /**
     * Removed all reserved installer files names
     *
     * @param string $_POST['dir_paths']        A semi-colon separated list of dir paths
     * @param string $_POST['file_paths']       A semi-colon separated list of file paths
     *
     * @return string   Returns all of the active directory filters as a ";" separated string
     */
    public function addQuickFilters()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('DUP_PRO_CTRL_Package_addQuickFilters', 'nonce');
/* @var $template DUP_PRO_Package_Template_Entity */
        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
                'dir_paths' => array(
                    'filter'  => FILTER_DEFAULT,
                    'flags'   => FILTER_REQUIRE_SCALAR,
                    'options' => array(
                        'default' => ''
                    )
                ),
                'file_paths' => array(
                    'filter'  => FILTER_DEFAULT,
                    'flags'   => FILTER_REQUIRE_SCALAR,
                    'options' => array(
                        'default' => ''
                    )
                ),
            ));
        $result = new DUP_PRO_CTRL_Result($this);
        try {
            DUP_PRO_U::hasCapability('export', DUP_PRO_U::SECURE_ISSUE_THROW);
        //CONTROLLER LOGIC
            // Need to update both the template and the temporary package because:
            // 1) We need to preserve preferences of this build for future manual builds - the manual template is used for this.
            // 2) Temporary package is used during this build - keeps all the settings/storage information.  Will be inserted into the package table after they ok the scan results.
            $template = DUP_PRO_Package_Template_Entity::get_manual_template();
            if ($template->archive_filter_on) {
                $template->archive_filter_dirs  = $template->archive_filter_dirs . (strlen($template->archive_filter_dirs) ? ';' : '') . SnapUtil::sanitizeNSChars($inputData['dir_paths']);
                $template->archive_filter_files = $template->archive_filter_files . (strlen($template->archive_filter_files) ? ';' : '') . SnapUtil::sanitizeNSChars($inputData['file_paths']);
            } else {
                $template->archive_filter_dirs  = SnapUtil::sanitizeNSChars($inputData['dir_paths']);
                $template->archive_filter_files = SnapUtil::sanitizeNSChars($inputData['file_paths']);
            }

            $template->archive_filter_dirs  = DUP_PRO_Archive::parseDirectoryFilter($template->archive_filter_dirs);
            $template->archive_filter_files = DUP_PRO_Archive::parseDirectoryFilter($template->archive_filter_files);
            if (!$template->archive_filter_on) {
                $template->archive_filter_exts = '';
            }

            $template->archive_filter_on = 1;
            $template->save();
/* @var $temporary_package DUP_PRO_Package */
            $temporary_package = DUP_PRO_Package::get_temporary_package();
            $temporary_package->Archive->FilterDirs  = $template->archive_filter_dirs;
            $temporary_package->Archive->FilterFiles = $template->archive_filter_files;
            $temporary_package->Archive->FilterOn    = 1;
            $temporary_package->set_temporary_package();
//Result
            $payload['filter-dirs']  = $temporary_package->Archive->FilterDirs;
            $payload['filter-files'] = $temporary_package->Archive->FilterFiles;
//RETURN RESULT
            //$test = ($success) ? DUP_PRO_CTRL_Status::SUCCESS : DUP_PRO_CTRL_Status::FAILED;
            $test = DUP_PRO_CTRL_Status::SUCCESS;
            $result->process($payload, $test);
        } catch (Exception $exc) {
            $result->processError($exc);
        }
    }

    /**
     * Enables the DupArchive setting and hides the notice box on package build step 1
     *
     * @param string $_POST['enable_duparchive']    A bool to enable DA
     *
     * @return bool     Returns true if the DupArchive flag is set to hide
     */
    public function switchDupArchiveNotice()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('DUP_PRO_CTRL_Package_switchDupArchiveNotice', 'nonce');
        DUP_PRO_LOG::trace("switch duparchive notice");
        $enable_duparchive = filter_input(INPUT_POST, 'enable_duparchive', FILTER_VALIDATE_BOOL);

        $result = new DUP_PRO_CTRL_Result($this);
        try {
            DUP_PRO_U::hasCapability('export', DUP_PRO_U::SECURE_ISSUE_THROW);

            //CONTROLLER LOGIC
            $global                            = DUP_PRO_Global_Entity::get_instance();
            $global->notices->dupArchiveSwitch = false;
            if ($enable_duparchive == 'true') {
                $global->archive_build_mode  = DUP_PRO_Archive_Build_Mode::DupArchive;
                $global->archive_compression = true;
            }
            $global->save();
//RETURN RESULT
            $status = DUP_PRO_CTRL_Status::SUCCESS;
            $result->process(null, $status);
        } catch (Exception $exc) {
            $result->processError($exc);
        }
    }

    /**
     * Toggles the feature gift icon on the packages page.  This should only show for new features and
     * once its clicked should hide.
     *
     * @param string $_POST['hide_gift_btn']    A bool to hide the gift button
     *
     * @return bool     Returns true if the Button is set to be hidden
     */
    public function toggleGiftFeatureButton()
    {
        DUP_PRO_Handler::init_error_handler();
        check_ajax_referer('DUP_PRO_CTRL_Package_toggleGiftFeatureButton', 'nonce');
        DUP_PRO_LOG::trace("toggle gift feature");
        $hide_gift_btn = filter_input(INPUT_POST, 'hide_gift_btn', FILTER_VALIDATE_BOOL);

        $result = new DUP_PRO_CTRL_Result($this);
        try {
            DUP_PRO_U::hasCapability('export', DUP_PRO_U::SECURE_ISSUE_THROW);

            //CONTROLLER LOGIC
            $global                            = DUP_PRO_Global_Entity::get_instance();
            $global->notices->dupArchiveSwitch = false;
            if ($hide_gift_btn == 'true') {
                $global->dupHidePackagesGiftFeatures = true;
            }

            $success = $global->save();
//RETURN RESULT
            $status = ($success) ? DUP_PRO_CTRL_Status::SUCCESS : DUP_PRO_CTRL_Status::FAILED;
            $result->process(null, $status);
        } catch (Exception $exc) {
            $result->processError($exc);
        }
    }
}
