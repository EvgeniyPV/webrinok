<?php

defined("ABSPATH") or die("");

use Duplicator\Libs\Snap\SnapIO;

class DUP_PRO_Web_Services_import extends DUP_PRO_Web_Services
{

    public function init()
    {
        $this->add_class_action('wp_ajax_duplicator_pro_import_upload', 'importUpload');
        $this->add_class_action('wp_ajax_duplicator_pro_import_package_delete', 'deletePackage');
        $this->add_class_action('wp_ajax_duplicator_pro_import_set_view_mode', 'setViewMode');
    }

    public static function importUploadCallback()
    {
        $out = array();

        if (!file_exists(DUPLICATOR_PRO_PATH_IMPORTS)) {
            SnapIO::mkdir(DUPLICATOR_PRO_PATH_IMPORTS, 0755, true);
        }

        //CONTROLLER LOGIC
        $archive_filename = isset($_FILES["file"]["name"]) ? sanitize_text_field($_FILES["file"]["name"]) : null;
        $temp_filename    = isset($_FILES["file"]["tmp_name"]) ? sanitize_text_field($_FILES["file"]["tmp_name"]) : null;
        $chunk            = filter_input(INPUT_POST, 'chunk', FILTER_VALIDATE_INT, array('options' => array('default' => false)));
        $chunks           = filter_input(INPUT_POST, 'chunks', FILTER_VALIDATE_INT, array('options' => array('default' => false)));
        $archive_filepath = DUPLICATOR_PRO_PATH_IMPORTS . '/' . $archive_filename;

        if (!preg_match(DUPLICATOR_PRO_ARCHIVE_REGEX_PATTERN, $archive_filename)) {
            throw new Exception(DUP_PRO_U::esc_html__("Invalid archive file name. Please use the valid archive file!"));
        }

        //CHUNK MODE
        if ($chunks !== false) {
            $archive_part_filepath = "{$archive_filepath}.part";

            // Clean last upload part leaved as it is (The situation in which user navigate to another url while uploading archive file path)
            if ($chunk === 0 && file_exists($archive_part_filepath)) {
                @unlink($archive_part_filepath);
            }

            if (($output = @fopen($archive_part_filepath, $chunks ? "ab" : "wb")) === false) {
                throw new Exception(sprintf(DUP_PRO_U::esc_html__('Could not write output: %s'), $archive_filepath));
            }

            if (($input = @fopen($temp_filename, "rb")) === false) {
                throw new Exception(sprintf(DUP_PRO_U::esc_html__('Could not read input: %s'), $temp_filename));
            }

            while ($buffer = fread($input, 8192)) {
                if (false === fwrite($output, $buffer)) {
                    throw new Exception(sprintf(DUP_PRO_U::esc_html__('Could not write output: %s'), $archive_filepath));
                }
            }

            fclose($output);
            fclose($input);

            $out['mode'] = 'chunk';
            if ($chunk == ($chunks - 1)) {
                if (rename($archive_part_filepath, $archive_filepath) === false) {
                    throw new Exception('Can\'t rename file part to file');
                }
                $out['status'] = 'complete';
            } else {
                $out['status'] = 'chunking';
            }
        } else { // DIRECT MODE
            if (move_uploaded_file($temp_filename, $archive_filepath) === false) {
                throw new Exception(DUP_PRO_U::esc_html__('Can\'t rename file part to file'));
            }
            $out['status'] = 'complete';
            $out['mode']   = 'direct';
        }

        if ($out['status'] === 'complete') {
            try {
                $importObj                = new DUP_PRO_Package_Importer($archive_filepath);
                $importObj->cleanImportFolder();
                $out['isImportable']      = $importObj->isImportable();
                $out['fullPath']          = $importObj->getFullPath();
                $out['installerPageLink'] = $importObj->getInstallerPageLink();
                $out['htmlDetails']       = $importObj->getHtmlDetails(false);
                $out['created']           = $importObj->getCreated();
                $out['invalidMessage']    = '';
            } catch (Exception $e) {
                $out['isImportable']    = false;
                $out['fullPath']        = $archive_filepath;
                $out['installePageUrl'] = '';
                $out['htmlDetails']     = sprintf(DUP_PRO_U::esc_html__('Problem on import, message: %s'), $e->getMessage());
                $out['created']         = '';
                $out['invalidMessage']  = $e->getMessage();
            }
        }
        return $out;
    }

    public function importUpload()
    {
        self::ajax_json_wrapper(array(__CLASS__, 'importUploadCallback'), 'duplicator_pro_import_upload', $_POST['nonce'], 'import');
    }

    public static function deletePackageCallback()
    {
        $inputData = filter_input_array(INPUT_POST, array(
            'path' => array(
                'filter'  => FILTER_SANITIZE_SPECIAL_CHARS,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => ''
                )
            ),
        ));

        if (empty($inputData['path'])) {
            throw new Exception(DUP_PRO_U::__("Invalid Request!"));
        }

        if (in_array($inputData['path'], DUP_PRO_Package_Importer::getArchiveList())) {
            if (unlink($inputData['path']) == false) {
                throw new Exception(DUP_PRO_U::__("Can\'t remove archvie!"));
            }
            DUP_PRO_Package_Importer::cleanImportFolder();
        }

        return true;
    }

    public function deletePackage()
    {
        self::ajax_json_wrapper(array(__CLASS__, 'deletePackageCallback'), 'duplicator_pro_import_package_delete', $_POST['nonce'], 'import');
    }

    public static function setViewModeCallback()
    {
        $viewMode = filter_input(INPUT_POST, 'view_mode', FILTER_SANITIZE_SPECIAL_CHARS);

        switch ($viewMode) {
            case DUP_PRO_CTRL_import::VIEW_MODE_ADVANCED:
            case DUP_PRO_CTRL_import::VIEW_MODE_BASIC:
                break;
            default:
                throw new Exception(DUP_PRO_U::__('Invalid view mode'));
        }

        if (!($userId = get_current_user_id())) {
            throw new Exception(DUP_PRO_U::__('Invalid current urser id'));
        }

        $archives = DUP_PRO_Package_Importer::getArchiveList();
        if ($viewMode == DUP_PRO_CTRL_import::VIEW_MODE_BASIC && count($archives) > 1) {
            update_user_meta($userId, DUP_PRO_CTRL_import::USER_META_VIEW_MODE, DUP_PRO_CTRL_import::VIEW_MODE_ADVANCED);
            throw new Exception(DUP_PRO_U::__('It is not possible to set the view mode to basic if the number of packages is more than one. Remove packages before performing this action.'));
        }

        if ($viewMode != DUP_PRO_CTRL_import::getViewMode()) {
            if (update_user_meta($userId, DUP_PRO_CTRL_import::USER_META_VIEW_MODE, $viewMode) == false) {
                throw new Exception(DUP_PRO_U::__('Can\'t update user meta value'));
            }
        }

        return DUP_PRO_CTRL_import::getViewMode();
    }

    public function setViewMode()
    {
        self::ajax_json_wrapper(array(__CLASS__, 'setViewModeCallback'), 'duplicator_pro_import_set_view_mode', $_POST['nonce'], 'import');
    }
}
