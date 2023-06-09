<?php

/**
 * Class to import archive
 *
 * Standard: PSR-2 (almost)
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package DUP_PRO
 * @subpackage classes/package
 * @copyright (c) 2017, Snapcreek LLC
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since 1.0.0
 *
 * @notes: Trace process time
 *  $timer01 = DUP_PRO_U::getMicrotime();
 *  DUP_PRO_LOG::trace("SCAN TIME-B = " . DUP_PRO_U::elapsedTime(DUP_PRO_U::getMicrotime(), $timer01));
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapURL;

class DUP_PRO_Package_Recover extends DUP_PRO_Package_Importer
{
    const MAX_PACKAGES_LIST         = 50;
    const OPTION_RECOVER_PACKAGE_ID = 'duplicator_pro_recover_point';
    const OUT_TO_HOURS_LIMIT        = 12;

    /**
     *
     * @var array
     */
    protected static $recoveablesPackages = null;

    /**
     *
     * @var self
     */
    protected static $instance = null;

    /**
     *
     * @var DUP_PRO_Package
     */
    protected $package = null;

    /**
     * @note This constructor should be protected but I can't change visibility before php 7.3 so I have to leave it public..
     * Use getRecoverPackage to take a recover object. Don't init it directly
     *
     * @param scrinf $path // valid archive patch
     * @throws Exception if file ins't valid
     */
    public function __construct($path, DUP_PRO_Package $package)
    {
        $this->package = $package;
        parent::__construct($path);
    }

    /**
     *
     * @return int
     */
    public function getPackageId()
    {
        return $this->package->ID;
    }

    /**
     * Return package life in hours
     *
     * @return int
     */
    public function getPackageLife()
    {
        $packageTime = strtotime($this->getCreated());
        $currentTime = strtotime('now');
        return max(0, floor(($currentTime - $packageTime) / 60 / 60));
    }

    /**
     * This function check if package is importable from scan info
     *
     * @param string $failMessage message if isn't importable
     *
     * @return boolean
     */
    public function isImportable(&$failMessage = null)
    {
        if (parent::isImportable($failMessage) === false) {
            return false;
        }

        //The scan logic is going to be refactored, so only use info from the scan.json, if it's too complex to use the
        // archive config info
        if ($this->scan->ARC->Status->HasFilteredCoreFolders) {
            $failMessage = DUP_PRO_U::__('The package is missing WordPress core folder(s)! ' .
                'It must include wp-admin, wp-content, wp-includes, uploads, plugins, and themes folders.');
            return false;
        }

        if ($this->info->mu_mode !== 0 && $this->info->mu_is_filtered) {
            $failMessage = DUP_PRO_U::__('The package is missing some subsites.');
            return false;
        }

        if ($this->info->dbInfo->tablesBaseCount != $this->info->dbInfo->tablesFinalCount) {
            $failMessage = DUP_PRO_U::__('The package is missing some of the site tables.');
            return false;
        }

        $failMessage = '';
        return true;
    }

    /**
     *
     * @return bool
     */
    public function isOutToDate()
    {
        return $this->getPackageLife() > self::OUT_TO_HOURS_LIMIT;
    }

    protected function getInstallerFolderPath()
    {
        return DUPLICATOR_PRO_PATH_RECOVER;
    }

    /**
     * return true if path have a recovery point sub path
     *
     * @param string $path
     * @return boolean
     */
    public static function isRecoverPath($path)
    {
        return (preg_match('/[\/]' . preg_quote(DUPLICATOR_PRO_SSDIR_NAME, '/') . '[\/]' . preg_quote(DUPLICATOR_PRO_RECOVER_DIR_NAME, '/') . '[\/]/', $path) === 1);
    }

    protected function getInstallerFolderUrl()
    {
        return DUPLICATOR_PRO_URL_RECOVER;
    }

    public function getInstallLink()
    {
        if (dirname($this->archive) === DUPLICATOR_PRO_SSDIR_PATH) {
            $archive = '..';
        } else {
            $archive = dirname($this->archive);
        }

        $queryStr = http_build_query(array(
            'archive'    => $archive,
            'dup_folder' => 'dup-installer-' . $this->info->packInfo->secondaryHash
        ));
        return $this->getInstallerFolderUrl() . '/' . $this->getInstallerName() . '?' . $queryStr;
    }

    public function getLauncherFileName()
    {

        $parseUrl     = SnapURL::parseUrl(get_home_url());
        $siteFileName = str_replace(array(':', '\\', '/', '.'), '_', $parseUrl['host'] . $parseUrl['path']);
        sanitize_file_name($siteFileName);

        return 'recover_' . sanitize_file_name($siteFileName) . '_' . date("Ymd_His", strtotime($this->getCreated())) . '.html';
    }

    public function getOverwriteParams()
    {
        $params        = parent::getOverwriteParams();
        $updDirs       = wp_upload_dir();
        $recoverParams = array(
            'template'        => array(
                'value' => 'recovery',
            ),
            'recovery-link'   => array(
                'value' => '',
            ),
            'restore-backup'  => array(
                'value'      => true,
                'formStatus' => 'st_infoonly'
            ),
            'archive_action'  => array(
                'value'      => 'removewpfiles',
                'formStatus' => 'st_infoonly'
            ),
            'url_new'         => array(
                'value'      => DUP_PRO_Archive::getOriginalUrls('home'),
                'formStatus' => 'st_infoonly'
            ),
            'path_new'        => array(
                'value'      => DUP_PRO_Archive::getOriginalPaths('home'),
                'formStatus' => 'st_infoonly'
            ),
            'siteurl'         => array(
                'value'      => site_url(),
                'formStatus' => 'st_infoonly'
            ),
            'path_core_new'   => array(
                'value'      => DUP_PRO_Archive::getOriginalPaths('abs'),
                'formStatus' => 'st_infoonly'
            ),
            'url_cont_new'    => array(
                'value'      => content_url(),
                'formStatus' => 'st_infoonly'
            ),
            'path_cont_new'   => array(
                'value'      => DUP_PRO_Archive::getOriginalPaths('wpcontent'),
                'formStatus' => 'st_infoonly'
            ),
            'url_upl_new'     => array(
                'value'      => $updDirs['baseurl'],
                'formStatus' => 'st_infoonly'
            ),
            'path_upl_new'    => array(
                'value'      => DUP_PRO_Archive::getOriginalPaths('uploads'),
                'formStatus' => 'st_infoonly'
            ),
            'url_plug_new'    => array(
                'value'      => plugins_url(),
                'formStatus' => 'st_infoonly'
            ),
            'path_plug_new'   => array(
                'value'      => DUP_PRO_Archive::getOriginalPaths('plugins'),
                'formStatus' => 'st_infoonly'
            ),
            'url_muplug_new'  => array(
                'value'      => WPMU_PLUGIN_URL,
                'formStatus' => 'st_infoonly'
            ),
            'path_muplug_new' => array(
                'value'      => DUP_PRO_Archive::getOriginalPaths('muplugins'),
                'formStatus' => 'st_infoonly'
            )
        );
        return array_merge($params, $recoverParams);
    }

    /**
     *
     * @param type $packageId
     * @return boolean\self
     */
    protected static function getInitRecoverPackageById($packageId)
    {
        if (!($package = DUP_PRO_Package::get_by_id($packageId))) {
            return false;
        }
        $archivePath = $package->get_local_package_file(DUP_PRO_Package_File_Type::Archive);
        return new self($archivePath, $package);
    }

    /**
     *
     * @param boolean $reset
     * @return boolean|self  // return false if recover package isn't set or recove package object
     */
    public static function getRecoverPackage($reset = false)
    {
        if (is_null(self::$instance) || $reset) {
            if (($packageId = get_option(self::OPTION_RECOVER_PACKAGE_ID)) == false) {
                self::$instance = null;
                return false;
            }

            if (!self::isPackageIdRecoveable($packageId, $reset)) {
                self::$instance = null;
                return false;
            }

            self::$instance = self::getInitRecoverPackageById($packageId);
        }

        return self::$instance;
    }

    /**
     *
     * @return boolean|int return false if not set or package id
     */
    public static function getRecoverPackageId()
    {
        if (DUP_PRO_CTRL_recovery::isDisallow()) {
            return false;
        }

        $recoverPackage = DUP_PRO_Package_Recover::getRecoverPackage();
        if ($recoverPackage instanceof DUP_PRO_Package_Recover) {
            return $recoverPackage->getPackageId();
        } else {
            return false;
        }
    }

    /**
     *
     * @param bool $emptyDir // if true remove recovery paackage files
     */
    public static function resetRecoverPackage($emptyDir = false)
    {
        self::$instance = null;
        if ($emptyDir) {
            SnapIO::emptyDir(DUPLICATOR_PRO_PATH_RECOVER);
        }
        delete_option(self::OPTION_RECOVER_PACKAGE_ID);
    }

    /**
     *
     * @param int $id // if mepty reset package
     *
     * @return boolean // false if fail
     */
    public static function setRecoveablePackage($id, &$errorMessage = null)
    {
        $id = (int) $id;

        self::resetRecoverPackage(true);

        if (empty($id)) {
            return true;
        }

        try {
            if (!self::isPackageIdRecoveable($id, true)) {
                throw new Exception('Package isn\'t in recoveable list');
            }

            if (!SnapIO::mkdir(DUPLICATOR_PRO_PATH_RECOVER, 0755, true)) {
                throw new Exception('Can\'t create recover package folder');
            }

            $recoverPackage = self::getInitRecoverPackageById($id);
            if (!$recoverPackage instanceof DUP_PRO_Package_Recover) {
                throw new Exception('Can\'t inizialize recover package');
            }

            $recoverPackage->prepareToInstall();

            if (!update_option(self::OPTION_RECOVER_PACKAGE_ID, $id)) {
                delete_option(self::OPTION_RECOVER_PACKAGE_ID);
                throw new Exception('Can\'t update ' . self::OPTION_RECOVER_PACKAGE_ID . ' option');
            }
        } catch (Exception $e) {
            delete_option(self::OPTION_RECOVER_PACKAGE_ID);
            $errorMessage = $e->getMessage();
            return false;
        } catch (Error $e) {
            delete_option(self::OPTION_RECOVER_PACKAGE_ID);
            $errorMessage = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * Check if passed package is avaiable for recovery and set $filteredData
     *
     * @param DUP_PRO_Package $package      package
     * @param array           $filteredData reference value where set filter data
     *
     * @return boolean
     */
    public static function isLocalPackageRecoveable(DUP_PRO_Package $package, &$filteredData = array())
    {
        return self::isLocalPackageImportable($package, $filteredData);
    }

    /**
     * Check if passed template is avaiable for recovery and set $filteredData
     *
     * @param DUP_PRO_Package_Template_Entity $template     template
     * @param array                           $filteredData reference value where set filter data
     *
     * @return boolean
     */
    public static function isTemplateRecoveable(DUP_PRO_Package_Template_Entity $template, &$filteredData = array())
    {
        return self::isTemplateImportable($template, $filteredData);
    }

    /**
     * Return recoveable packages list
     * [ packageId => [
     *       'id'       => $package->ID,
     *       'created'  => $package->Created,
     *       'nameHash' => $package->NameHash,
     *       'name'     => $package->Name
     *    ]
     * ]
     *
     * @return array
     */
    public static function getRecoverablesPackages($reset = false)
    {
        if (is_null(self::$recoveablesPackages) || $reset) {
            self::$recoveablesPackages = array();
            DUP_PRO_Package::by_status_callback(
                array(__CLASS__, 'recoverablePackageCheck'),
                array(
                    array('op' => '>=', 'status' => DUP_PRO_PackageStatus::COMPLETE)
                ),
                self::MAX_PACKAGES_LIST,
                0,
                '`created` DESC'
            );
        }
        self::addRecoverPackageToListIfNotExists();

        return self::$recoveablesPackages;
    }

    /**
     * Add current recovery packeg in list if not exists
     *
     * @return bool true if ok
     */
    protected static function addRecoverPackageToListIfNotExists()
    {
        if (($recoverPackageId = get_option(self::OPTION_RECOVER_PACKAGE_ID)) === false) {
            return true;
        }

        if (in_array($recoverPackageId, array_keys(self::$recoveablesPackages))) {
            return true;
        }

        $recoverPackage = DUP_PRO_Package::get_by_id($recoverPackageId);
        if (!$recoverPackage instanceof DUP_PRO_Package) {
            return false;
        }

        return self::recoverablePackageCheck($recoverPackage);
    }

    /**
     * return true if packages id is reacoveable
     *
     * @param int     $id    package id
     * @param boolean $reset if true reset packages list
     *
     * @return boolean
     */
    public static function isPackageIdRecoveable($id, $reset = false)
    {
        if (DUP_PRO_CTRL_recovery::isDisallow()) {
            return false;
        }

        return in_array($id, self::getRecoverablesPackagesIds($reset));
    }

    /**
     * Get recoveable pacakge ids
     *
     * @param bool $reset if true reset list
     *
     * @return int[]
     */
    public static function getRecoverablesPackagesIds($reset = false)
    {
        return array_keys(self::getRecoverablesPackages($reset));
    }

    /**
     * Check if package is recoveable
     *
     * @param DUP_PRO_Package $package
     *
     * @return bool true if is added
     */
    public static function recoverablePackageCheck(DUP_PRO_Package $package)
    {
        if (version_compare($package->Version, self::IMPORT_ENABLE_MIN_VERSION, '<')) {
            return false;
        }

        $archivePath = $package->get_local_package_file(DUP_PRO_Package_File_Type::Archive);
        if (!file_exists($archivePath)) {
            return false;
        }

        if ($package->Archive->ExportOnlyDB) {
            return false;
        }

        if (!empty($package->Multisite->FilterSites)) {
            return false;
        }

        if ($package->Archive->hasWpCoreFolderFiltered()) {
            return false;
        }

        if ($package->Database->info->tablesBaseCount !== $package->Database->info->tablesFinalCount) {
            return false;
        }

        self::$recoveablesPackages[$package->ID] = array(
            'id'       => $package->ID,
            'created'  => $package->Created,
            'nameHash' => $package->NameHash,
            'name'     => $package->Name
        );
        return true;
    }
}
