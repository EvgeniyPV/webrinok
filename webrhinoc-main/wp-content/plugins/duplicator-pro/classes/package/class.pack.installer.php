<?php

/**
 * Classes for building the package installer extra files
 *
 * @copyright (c) 2017, Snapcreek LLC
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\DupArchive\DupArchiveEngine;
use Duplicator\Libs\Snap\SnapCode;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapJson;
use Duplicator\Libs\Snap\SnapOrigFileManager;
use Duplicator\Libs\Snap\SnapWP;

require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/entities/class.system.global.entity.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/utilities/class.u.shell.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/class.archive.config.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/entities/class.brand.entity.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/class.password.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/lib/config/class.wp.config.tranformer.php');

class DUP_PRO_Installer
{
    const DEFAULT_INSTALLER_FILE_NAME_WITHOUT_HASH = 'installer.php';
    const CONFIG_ORIG_FILE_FOLDER_PREFIX           = 'source_site_';
    const CONFIG_ORIG_FILE_USERINI_ID              = 'userini';
    const CONFIG_ORIG_FILE_HTACCESS_ID             = 'htaccess';
    const CONFIG_ORIG_FILE_WPCONFIG_ID             = 'wpconfig';
    const CONFIG_ORIG_FILE_PHPINI_ID               = 'phpini';
    const CONFIG_ORIG_FILE_WEBCONFIG_ID            = 'webconfig';

    public $File;
    public $Size             = 0;
    //SETUP
    public $OptsSecureOn;
    public $OptsSecurePass;
    public $OptsSkipScan;
    //BASIC
    public $OptsDBHost;
    public $OptsDBName;
    public $OptsDBUser;
    //CPANEL
    public $OptsCPNLHost     = '';
    public $OptsCPNLUser     = '';
    public $OptsCPNLPass     = '';
    public $OptsCPNLEnable   = false;
    public $OptsCPNLConnect  = false;
    //CPANEL DB
    //1 = Create New, 2 = Connect Remove
    public $OptsCPNLDBAction = 'create';
    public $OptsCPNLDBHost   = '';
    public $OptsCPNLDBName   = '';
    public $OptsCPNLDBUser   = '';

    /**
     *
     * @var SnapOrigFileManager
     */
    protected $origFileManger = null;

    /**
     *
     * @var DUP_PRO_Package
     */
    protected $Package;
    public $numFilesAdded = 0;
    public $numDirsAdded  = 0;

    /**
     *
     * @var DupProWPConfigTransformer
     */
    private $configTransformer = null;

    /**
     *
     * @param DUP_PRO_Package $package
     */
    public function __construct(DUP_PRO_Package $package)
    {
        $this->Package        = $package;
        $this->origFileManger = new SnapOrigFileManager(DUP_PRO_Archive::getArchiveListPaths('home'), DUPLICATOR_PRO_SSDIR_PATH_TMP, $this->Package->get_package_hash());

        if (($wpConfigPath = DUP_PRO_Archive::getWPConfigFilePath()) !== false) {
            $this->configTransformer = new DupProWPConfigTransformer($wpConfigPath);
        }
    }

    public function __destruct()
    {
        $this->Package        = null;
        $this->origFileManger = null;
    }

    public function get_safe_filepath()
    {
        $file_path = apply_filters('duplicator_pro_installer_file_path', SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH . "/{$this->File}"));
        return $file_path;
    }

    public function get_orig_filename()
    {
        return $this->File;
    }

    public function get_url()
    {
        return DUPLICATOR_PRO_SSDIR_URL . "/{$this->File}";
    }

    /**
     *
     * @param DUP_PRO_Build_Progress $build_progress
     */
    public function build(DUP_PRO_Build_Progress $build_progress)
    {
        /* @var $package DUP_PRO_Package */
        DUP_PRO_LOG::trace("building installer");
        $success = false;
        if ($this->create_enhanced_installer_files()) {
            $success = $this->add_extra_files();
        }

        if ($success) {
            $build_progress->installer_built = true;
        } else {
            DUP_PRO_LOG::infoTrace("Error in create_enhanced_installer_files, set build failed");
            $build_progress->failed = true;
        }
    }

    private function create_enhanced_installer_files()
    {
        $success = false;
        if ($this->create_enhanced_installer()) {
            $success = $this->create_archive_config_file();
        } else {
            DUP_PRO_LOG::infoTrace("Error in create_enhanced_installer, set build failed");
        }

        return $success;
    }

    private function create_enhanced_installer()
    {
        $global                 = DUP_PRO_Global_Entity::get_instance();
        $success                = true;
        $archive_filepath       = SnapIO::safePath("{$this->Package->StorePath}/{$this->Package->Archive->File}");
        $installer_filepath     = apply_filters('duplicator_pro_installer_file_path', SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH_TMP) . "/{$this->Package->NameHash}_{$global->installer_base_name}");
        $template_filepath      = DUPLICATOR_PRO_PLUGIN_PATH . '/installer/installer.tpl';
        // Replace the @@ARCHIVE@@ token
        $header = <<<HEADER
<?php
/* ------------------------------ NOTICE ----------------------------------

If you're seeing this text when browsing to the installer, it means your
web server is not set up properly.

Please contact your host and ask them to enable "PHP" processing on your
account.
----------------------------- NOTICE --------------------------------- */
?>
HEADER;
        $installer_contents     = $header . SnapCode::getSrcClassCode($template_filepath, false) . "\n/* DUPLICATOR_PRO_INSTALLER_EOF */";
        // $installer_contents     = file_get_contents($template_filepath);
        // $csrf_class_contents = file_get_contents($csrf_class_filepath);

        if ($this->Package->build_progress->current_build_mode == DUP_PRO_Archive_Build_Mode::DupArchive) {
            $dupLib = DUPLICATOR_PRO_PLUGIN_PATH . '/src/Libs/DupArchive/';
            $dupExpanderCoder = '';
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'DupArchive.php') . "\n";
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'DupArchiveExpandBasicEngine.php') . "\n";
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'Headers/DupArchiveReaderDirectoryHeader.php') . "\n";
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'Headers/DupArchiveReaderFileHeader.php') . "\n";
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'Headers/DupArchiveReaderGlobHeader.php') . "\n";
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'Headers/DupArchiveReaderHeader.php') . "\n";
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'Headers/DupArchiveHeaderU.php') . "\n";
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'Info/DupArchiveExpanderInfo.php') . "\n";
            if (strlen($dupExpanderCoder) == 0) {
                DUP_PRO_Log::error(DUP_PRO_U::__('Error reading DupArchive expander'), DUP_PRO_U::__('Error reading DupArchive expander'), false);
                return false;
            }
        } else {
            $dupExpanderCoder = '';
        }

        $search_array           = array('@@ARCHIVE@@', '@@VERSION@@', '@@ARCHIVE_SIZE@@', '@@PACKAGE_HASH@@', '@@SECONDARY_PACKAGE_HASH@@', '@@DUPARCHIVE_MINI_EXPANDER@@');
        $package_hash           = $this->Package->get_package_hash();
        $secondary_package_hash = $this->Package->getSecondaryPackageHash();
        $replace_array          = array($this->Package->Archive->File, DUPLICATOR_PRO_VERSION, @filesize($archive_filepath), $package_hash, $secondary_package_hash, $dupExpanderCoder);
        $installer_contents     = str_replace($search_array, $replace_array, $installer_contents);
        if (@file_put_contents($installer_filepath, $installer_contents) === false) {
            DUP_PRO_Log::error(DUP_PRO_U::__('Error writing installer contents'), DUP_PRO_U::__("Couldn't write to $installer_filepath"), false);
            $success = false;
        }

        if ($success) {
            $storePath  = "{$this->Package->StorePath}/{$this->File}";
            $this->Size = @filesize($storePath);
        }

        return $success;
    }

    /**
     * Create archive.txt file
     *
     * @global type $wpdb
     * @return boolean
     */
    private function create_archive_config_file()
    {
        global $wpdb;
        if (is_multisite()) {
            restore_current_blog();
        }

        $global                  = DUP_PRO_Global_Entity::get_instance();
        $success                 = true;
        $archive_config_filepath = SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH_TMP) . "/{$this->Package->NameHash}_archive.txt";
        $ac                      = new DUP_PRO_Archive_Config();
        $extension               = strtolower($this->Package->Archive->Format);
        $hasher                  = new DUP_PRO_PasswordHash(8, false);
        $pass_hash               = $hasher->HashPassword($this->Package->Installer->OptsSecurePass);

        //READ-ONLY: COMPARE VALUES
        $ac->created     = $this->Package->Created;
        $ac->version_dup = DUPLICATOR_PRO_VERSION;
        $ac->version_wp  = $this->Package->VersionWP;
        $ac->version_db  = $this->Package->VersionDB;
        $ac->version_php = $this->Package->VersionPHP;
        $ac->version_os  = $this->Package->VersionOS;
        $ac->dup_type    = 'pro';
        $ac->dbInfo      = $this->Package->Database->info;
        $ac->packInfo    = array(
            'packageId'     => $this->Package->ID,
            'packageName'   => $this->Package->Name,
            'packageHash'   => $this->Package->get_package_hash(),
            'secondaryHash' => $this->Package->getSecondaryPackageHash()
        );
        $ac->fileInfo    = array(
            'dirCount'  => $this->Package->Archive->DirCount,
            'fileCount' => $this->Package->Archive->FileCount,
            'size'      => $this->Package->Archive->Size
        );
        $ac->wpInfo      = $this->getWpInfo();

        //READ-ONLY: GENERAL
        $ac->installer_base_name   = $global->installer_base_name;
        $ac->installer_backup_name = $this->getInstallerBackupName();
        $ac->package_name          = "{$this->Package->NameHash}_archive.{$extension}";
        $ac->package_hash          = $this->Package->get_package_hash();
        $ac->package_notes         = $this->Package->Notes;
        $ac->opts_delete           = SnapJson::jsonEncode($GLOBALS['DUPLICATOR_PRO_OPTS_DELETE']);
        $ac->blogname              = sanitize_text_field(get_option('blogname'));
        $ac->wproot                = duplicator_pro_get_home_path();
        $ac->relative_content_dir  = str_replace(ABSPATH, '', WP_CONTENT_DIR);
        $ac->relative_plugins_dir  = str_replace(ABSPATH, '', WP_PLUGIN_DIR);
        $ac->relative_plugins_dir  = str_replace($ac->wproot, '', $ac->relative_plugins_dir);
        $ac->relative_theme_dirs   = get_theme_roots();
        if (is_array($ac->relative_theme_dirs)) {
            foreach ($ac->relative_theme_dirs as $key => $dir) {
                if (strpos($dir, $ac->wproot) === false) {
                    $ac->relative_theme_dirs[$key] = $ac->relative_content_dir . $dir;
                } else {
                    $ac->relative_theme_dirs[$key] = str_replace($ac->wproot, '', $dir);
                }
            }
        } else {
            $ac->relative_theme_dirs = array();
            $dir                     = get_theme_roots();
            if (strpos($dir, $ac->wproot) === false) {
                $ac->relative_theme_dirs[] = $ac->relative_content_dir . $dir;
            } else {
                $ac->relative_theme_dirs[] = str_replace($ac->wproot, '', $dir);
            }
        }
        $ac->exportOnlyDB = $this->Package->Archive->ExportOnlyDB;
        $ac->wplogin_url  = wp_login_url();
        $ac->skipscan     = $this->Package->Installer->OptsSkipScan;

        //PRE-FILLED: GENERAL
        $ac->secure_on   = (bool) $this->Package->Installer->OptsSecureOn;
        $ac->secure_pass = $ac->secure_on ? $pass_hash : '';

        //MULTISITE
        $ac->mu_mode        = DUP_PRO_MU::getMode();
        $ac->wp_tableprefix = $wpdb->base_prefix;
        $ac->mu_generation  = DUP_PRO_MU::getGeneration();
        $ac->mu_is_filtered = !empty($this->Package->Multisite->FilterSites) ? true : false;
        $ac->mu_siteadmins  = array_values(get_super_admins());
        $filteredTables     = ($this->Package->Database->FilterOn && isset($this->Package->Database->FilterTables)) ? explode(',', $this->Package->Database->FilterTables) : array();
        $ac->subsites       = DUP_PRO_MU::getSubsites($this->Package->Multisite->FilterSites, $filteredTables, $this->Package->Archive->FilterInfo->Dirs->Instance);
        if ($ac->subsites === false) {
            DUP_PRO_Log::error("Error get subsites", "Couldn't get subisites", false);
            $success = false;
        }
        $ac->main_site_id = DUP_PRO_MU::get_main_site_id();

        //BRAND
        $ac->brand = $this->the_brand_setup($this->Package->Brand_ID);

        //LICENSING
        $ac->license_limit = $global->license_limit;

        // OVERWRITE PARAMS
        $ac->overwriteInstallerParams = apply_filters('duplicator_pro_overwrite_params_data', $this->getPrefillParams());
        $ac->wp_content_dir_base_name = '';
        $json                         = SnapJson::jsonEncodePPrint($ac);
        DUP_PRO_LOG::traceObject('json', $json);
        if (file_put_contents($archive_config_filepath, $json) === false) {
            DUP_PRO_Log::error("Error writing archive config", "Couldn't write archive config at $archive_config_filepath", false);
            $success = false;
        }

        return $success;
    }

    private function getPrefillParams()
    {
        $result = array();
        if (strlen($this->Package->Installer->OptsDBHost) > 0) {
            $result['dbhost'] = array('value' => $this->Package->Installer->OptsDBHost);
        }

        if (strlen($this->Package->Installer->OptsDBName) > 0) {
            $result['dbname'] = array('value' => $this->Package->Installer->OptsDBName);
        }

        if (strlen($this->Package->Installer->OptsDBUser) > 0) {
            $result['dbuser'] = array('value' => $this->Package->Installer->OptsDBUser);
        }

        if (filter_var($this->Package->Installer->OptsCPNLEnable, FILTER_VALIDATE_BOOLEAN)) {
            $result['view_mode'] = array('value' => 'cpnl');
        }

        if (strlen($this->Package->Installer->OptsCPNLDBAction) > 0) {
            $result['cpnl-dbaction'] = array('value' => $this->Package->Installer->OptsCPNLDBAction);
        }

        if (strlen($this->Package->Installer->OptsCPNLHost) > 0) {
            $result['cpnl-host'] = array('value' => $this->Package->Installer->OptsCPNLHost);
        }

        if (strlen($this->Package->Installer->OptsCPNLUser) > 0) {
            $result['cpnl-user'] = array('value' => $this->Package->Installer->OptsCPNLUser);
        }

        if (strlen($this->Package->Installer->OptsCPNLPass) > 0) {
            $result['cpnl-pass'] = array('value' => $this->Package->Installer->OptsCPNLPass);
        }

        if (strlen($this->Package->Installer->OptsCPNLDBHost) > 0) {
            $result['cpnl-dbhost'] = array('value' => $this->Package->Installer->OptsCPNLDBHost);
        }

        if (strlen($this->Package->Installer->OptsCPNLDBName) > 0) {
            $result['cpnl-dbname-txt'] = array('value' => $this->Package->Installer->OptsCPNLDBName);
        }

        if (strlen($this->Package->Installer->OptsCPNLDBUser) > 0) {
            $result['cpnl-dbuser-txt'] = array('value' => $this->Package->Installer->OptsCPNLDBUser);
        }

        return $result;
    }

    /**
     * return list of extra files to att to archive
     *
     * @param bool $checkExists
     * @return array
     * @throws Exception
     */
    private function getExtraFilesLists($checkExists = true)
    {
        $global = DUP_PRO_Global_Entity::get_instance();
        $result = array();

        $result[] = array(
            'sourcePath'  => apply_filters('duplicator_pro_installer_file_path', SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH_TMP) . "/{$this->Package->NameHash}_{$global->installer_base_name}"),
            'archivePath' => $this->getInstallerBackupName(),
            'label'       => 'installer backup file'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR_PRO_PLUGIN_PATH . '/installer/dup-installer',
            'archivePath' => '/',
            'label'       => 'dup installer folder'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR_PRO_PLUGIN_PATH . '/src/Libs/Snap',
            'archivePath' => 'dup-installer/libs/',
            'label'       => 'dup snaplib folder'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR_PRO_PLUGIN_PATH . '/src/Libs/DupArchive',
            'archivePath' => 'dup-installer/libs/',
            'label'       => 'dup snaplib folder'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR_PRO_PLUGIN_PATH . '/lib/config',
            'archivePath' => 'dup-installer/lib/',
            'label'       => 'lib config folder'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR_PRO_PLUGIN_PATH . '/lib/certificates',
            'archivePath' => 'dup-installer/lib/',
            'label'       => 'SSL certificates'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR____PATH . '/assets/js/duplicator-tooltip.js',
            'archivePath' => 'dup-installer/assets/js/duplicator-tooltip.js',
            'label'       => 'Duplicator tooltip script'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR____PATH . '/assets/js/popper',
            'archivePath' => 'dup-installer/assets/js/',
            'label'       => 'popper js'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR____PATH . '/assets/js/tippy',
            'archivePath' => 'dup-installer/assets/js/',
            'label'       => 'tippy js'
        );

        $result[] = array(
            'sourcePath'  => $this->origFileManger->getMainFolder(),
            'archivePath' => 'dup-installer/',
            'label'       => 'original files folder'
        );

        $result[] = array(
            'sourcePath'  => SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH_TMP) . "/{$this->Package->NameHash}_archive.txt",
            'archivePath' => $this->getArchiveTxtFilePath(),
            'label'       => 'archive descriptor file'
        );

        $result[] = array(
            'sourcePath'  => SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH_TMP) . "/{$this->Package->NameHash}_scan.json",
            'archivePath' => $this->getEmbeddedScanFilePath(),
            'label'       => 'scan file'
        );

        $result[] = array(
            'sourcePath'  => SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH_TMP) . '/' . $this->Package->NameHash . DUP_PRO_Archive::FILES_LIST_FILE_NAME_SUFFIX,
            'archivePath' => $this->getEmbeddedScanFileList(),
            'label'       => 'files list file'
        );

        $result[] = array(
            'sourcePath'  => SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH_TMP) . '/' . $this->Package->NameHash . DUP_PRO_Archive::DIRS_LIST_FILE_NAME_SUFFIX,
            'archivePath' => $this->getEmbeddedScanDirList(),
            'label'       => 'folders list file'
        );

        $result[] = array(
            'sourcePath'  => $this->getManualExtractFilePath(),
            'archivePath' => $this->getEmbeddedManualExtractFilePath(),
            'label'       => 'manual extract file'
        );

        foreach (\Duplicator\Core\Addons\AddonsManager::getInstance()->getEnabledAddons() as $addon) {
            if (!is_readable($addon->getAddonInstallerPath())) {
                continue;
            }

            $result[] = array(
                'sourcePath'  => $addon->getAddonInstallerPath(),
                'archivePath' => 'dup-installer/addons/',
                'label'       => 'addon ' . $addon->getSlug()
            );
        }

        // sql file should be the last one
        if ($this->Package->build_progress->current_build_mode == DUP_PRO_Archive_Build_Mode::Shell_Exec) {
            $result[] = array(
                'sourcePath'  => SnapIO::safePath("{$this->Package->StorePath}/{$this->Package->Database->File}"),
                'archivePath' => $this->getEmbeddedSqlFile(),
                'label'       => 'Sql dump file'
            );
        }

        if ($checkExists) {
            foreach ($result as $item) {
                if (!is_readable($item['sourcePath'])) {
                    throw new Exception('INSTALLER FILES: ' . $result['label'] . ' doesn\'t exist ' . $item['sourcePath']);
                }
            }
        }

        return $result;
    }

    /**
     * get wpInfo object
     *
     * @return \stdClass
     */
    private function getWpInfo()
    {
        $wpInfo               = new stdClass();
        $wpInfo->version      = $this->Package->VersionWP;
        $wpInfo->is_multisite = is_multisite();
        if (function_exists('get_current_network_id')) {
            $wpInfo->network_id = get_current_network_id();
        } else {
            $wpInfo->network_id = 1;
        }

        $wpInfo->targetRoot          = DUP_PRO_Archive::getTargetRootPath();
        $wpInfo->targetPaths         = DUP_PRO_Archive::getScanPaths();
        $wpInfo->adminUsers          = DUP_PRO_WP_U::getAdminUserLists();
        $wpInfo->configs             = new stdClass();
        $wpInfo->configs->defines    = new stdClass();
        $wpInfo->configs->realValues = new stdClass();
        $wpInfo->plugins             = $this->getPluginsInfo();
        $wpInfo->themes              = $this->getThemesInfo();

        $this->addDefineIfExists($wpInfo->configs->defines, 'ABSPATH');
        $this->addDefineIfExists($wpInfo->configs->defines, 'DB_CHARSET');
        $this->addDefineIfExists($wpInfo->configs->defines, 'DB_COLLATE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'AUTH_KEY');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SECURE_AUTH_KEY');
        $this->addDefineIfExists($wpInfo->configs->defines, 'LOGGED_IN_KEY');
        $this->addDefineIfExists($wpInfo->configs->defines, 'NONCE_KEY');
        $this->addDefineIfExists($wpInfo->configs->defines, 'AUTH_SALT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SECURE_AUTH_SALT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'LOGGED_IN_SALT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'NONCE_SALT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_SITEURL');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_HOME');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_CONTENT_DIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_CONTENT_URL');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_PLUGIN_DIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_PLUGIN_URL');
        $this->addDefineIfExists($wpInfo->configs->defines, 'PLUGINDIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'UPLOADS');
        $this->addDefineIfExists($wpInfo->configs->defines, 'AUTOSAVE_INTERVAL');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_POST_REVISIONS');
        $this->addDefineIfExists($wpInfo->configs->defines, 'COOKIE_DOMAIN');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_ALLOW_MULTISITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'ALLOW_MULTISITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'MULTISITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'DOMAIN_CURRENT_SITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'PATH_CURRENT_SITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SITE_ID_CURRENT_SITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'BLOG_ID_CURRENT_SITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SUBDOMAIN_INSTALL');
        $this->addDefineIfExists($wpInfo->configs->defines, 'VHOST');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SUNRISE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'NOBLOGREDIRECT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_DEBUG');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SCRIPT_DEBUG');
        $this->addDefineIfExists($wpInfo->configs->defines, 'CONCATENATE_SCRIPTS');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_DEBUG_LOG');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_DEBUG_DISPLAY');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_MEMORY_LIMIT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_MAX_MEMORY_LIMIT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_CACHE');

        // wp super cache define
        $this->addDefineIfExists($wpInfo->configs->defines, 'WPCACHEHOME');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_TEMP_DIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'CUSTOM_USER_TABLE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'CUSTOM_USER_META_TABLE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WPLANG');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_LANG_DIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SAVEQUERIES');
        $this->addDefineIfExists($wpInfo->configs->defines, 'FS_CHMOD_DIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'FS_CHMOD_FILE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'FS_METHOD');
        /**
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_BASE');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_CONTENT_DIR');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_PLUGIN_DIR');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_PUBKEY');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_PRIKEY');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_USER');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_PASS');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_HOST');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_SSL');
         * */
        $this->addDefineIfExists($wpInfo->configs->defines, 'ALTERNATE_WP_CRON');
        $this->addDefineIfExists($wpInfo->configs->defines, 'DISABLE_WP_CRON');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_CRON_LOCK_TIMEOUT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'COOKIEPATH');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SITECOOKIEPATH');
        $this->addDefineIfExists($wpInfo->configs->defines, 'ADMIN_COOKIE_PATH');
        $this->addDefineIfExists($wpInfo->configs->defines, 'PLUGINS_COOKIE_PATH');
        $this->addDefineIfExists($wpInfo->configs->defines, 'TEMPLATEPATH');
        $this->addDefineIfExists($wpInfo->configs->defines, 'STYLESHEETPATH');
        $this->addDefineIfExists($wpInfo->configs->defines, 'EMPTY_TRASH_DAYS');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_ALLOW_REPAIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'DO_NOT_UPGRADE_GLOBAL_TABLES');
        $this->addDefineIfExists($wpInfo->configs->defines, 'DISALLOW_FILE_EDIT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'DISALLOW_FILE_MODS');
        $this->addDefineIfExists($wpInfo->configs->defines, 'FORCE_SSL_ADMIN');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_HTTP_BLOCK_EXTERNAL');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_ACCESSIBLE_HOSTS');
        $this->addDefineIfExists($wpInfo->configs->defines, 'AUTOMATIC_UPDATER_DISABLED');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_AUTO_UPDATE_CORE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'IMAGE_EDIT_OVERWRITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WPMU_PLUGIN_DIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WPMU_PLUGIN_URL');
        $this->addDefineIfExists($wpInfo->configs->defines, 'MUPLUGINDIR');

        $originalUrls                               = DUP_PRO_Archive::getOriginalUrls();
        $wpInfo->configs->realValues->siteUrl       = $originalUrls['abs'];
        $wpInfo->configs->realValues->homeUrl       = $originalUrls['home'];
        $wpInfo->configs->realValues->contentUrl    = $originalUrls['wpcontent'];
        $wpInfo->configs->realValues->uploadBaseUrl = $originalUrls['uploads'];
        $wpInfo->configs->realValues->pluginsUrl    = $originalUrls['plugins'];
        $wpInfo->configs->realValues->mupluginsUrl  = $originalUrls['muplugins'];
        $wpInfo->configs->realValues->themesUrl     = $originalUrls['themes'];
        $wpInfo->configs->realValues->originalPaths = array();
        $originalpaths                              = DUP_PRO_Archive::getOriginalPaths();
        foreach ($originalpaths as $key => $val) {
            $wpInfo->configs->realValues->originalPaths[$key] = rtrim($val, '\\/');
        }
        $wpInfo->configs->realValues->archivePaths = array_merge($wpInfo->configs->realValues->originalPaths, DUP_PRO_Archive::getArchiveListPaths());
        return $wpInfo;
    }

    /**
     * check if $define is defined and add a prop to $obj
     *
     * @param object $obj
     * @param string $define
     * @return boolean return true if define is added of false
     *
     */
    private function addDefineIfExists($obj, $define)
    {
        if (defined($define)) {
            $obj->{$define}             = new StdClass();
            $obj->{$define}->value      = constant($define);
            $obj->{$define}->inWpConfig = $this->configTransformer->exists('constant', $define);
            return true;
        } else {
            return false;
        }
    }

    /**
     * get plugins array info with multisite, must-use and drop-ins
     *
     * @return array
     */
    public function getPluginsInfo()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // parse all plugins
        $result = array();
        foreach (get_plugins() as $path => $plugin) {
            $result[$path]                  = self::getPluginArrayData($path, $plugin);
            $result[$path]['networkActive'] = is_plugin_active_for_network($path);
            if (!is_multisite()) {
                $result[$path]['active'] = is_plugin_active($path);
            } else {
                // if is _multisite the active value is an array with the blog ids list where the plugin is active
                $result[$path]['active'] = array();
            }
        }

        // if is _multisite the active value is an array with the blog ids list where the plugin is active
        if (is_multisite()) {
            foreach (SnapWP::getSitesIds() as $siteId) {
                switch_to_blog($siteId);
                foreach ($result as $path => $plugin) {
                    if (!$result[$path]['networkActive'] && is_plugin_active($path)) {
                        $result[$path]['active'][] = $siteId;
                    }
                }
                restore_current_blog();
            }
        }

        // parse all must use plugins
        foreach (get_mu_plugins() as $path => $plugin) {
            $result[$path]            = self::getPluginArrayData($path, $plugin);
            $result[$path]['mustUse'] = true;
        }

        // parse all dropins plugins
        foreach (get_dropins() as $path => $plugin) {
            $result[$path]            = self::getPluginArrayData($path, $plugin);
            $result[$path]['dropIns'] = true;
        }

        return $result;
    }

    /**
     * return plugin formatted data from plugin info
     * plugin info =  Array (
     *      [Name] => Hello Dolly
     *      [PluginURI] => http://wordpress.org/extend/plugins/hello-dolly/
     *      [Version] => 1.6
     *      [Description] => This is not just ...
     *      [Author] => Matt Mullenweg
     *      [AuthorURI] => http://ma.tt/
     *      [TextDomain] =>
     *      [DomainPath] =>
     *      [Network] =>
     *      [Title] => Hello Dolly
     *      [AuthorName] => Matt Mullenweg
     * )
     *
     * @param string $slug      // plugin slug
     * @param array $plugin     // pluhin info from get_plugins function
     * @return array
     */
    protected static function getPluginArrayData($slug, $plugin)
    {
        return array(
            'slug'          => $slug,
            'name'          => $plugin['Name'],
            'version'       => $plugin['Version'],
            'pluginURI'     => $plugin['PluginURI'],
            'author'        => $plugin['Author'],
            'authorURI'     => $plugin['AuthorURI'],
            'description'   => $plugin['Description'],
            'title'         => $plugin['Title'],
            'networkActive' => false,
            'active'        => false,
            'mustUse'       => false,
            'dropIns'       => false
        );
    }

    /**
     * get themes array info with active template, stylesheet
     *
     * @return array
     */
    public function getThemesInfo()
    {
        if (!function_exists('wp_get_themes')) {
            require_once ABSPATH . 'wp-admin/includes/theme.php';
        }

        foreach (wp_get_themes() as $slug => $theme) {
            $result[$slug] = self::getThemeArrayData($theme);
        }

        if (is_multisite()) {
            foreach (SnapWP::getSitesIds() as $siteId) {
                switch_to_blog($siteId);
                $stylesheet = get_stylesheet();
                if (isset($result[$stylesheet])) {
                    $result[$stylesheet]['isActive'][] = $siteId;
                }
                restore_current_blog();
            }
        } else {
            $stylesheet = get_stylesheet();
            if (isset($result[$stylesheet])) {
                $result[$stylesheet]['isActive'] = true;
            }
        }

        return $result;
    }

    /**
     * return plugin formatted data from plugin info
     *
     * @param WP_Theme $theme instance of WP Core class WP_Theme. theme info from get_themes function
     * @return array
     */
    protected static function getThemeArrayData(WP_Theme $theme)
    {
        $slug   = $theme->get_stylesheet();
        $parent = $theme->parent();
        return array(
            'slug'         => $slug,
            'themeName'    => $theme->get('Name'),
            'version'      => $theme->get('Version'),
            'themeURI'     => $theme->get('ThemeURI'),
            'parentTheme'  => (false === $parent) ? false : $parent->get_stylesheet(),
            'template'     => $theme->get_template(),
            'stylesheet'   => $theme->get_stylesheet(),
            'description'  => $theme->get('Description'),
            'author'       => $theme->get('Author'),
            "authorURI"    => $theme->get('AuthorURI'),
            'tags'         => $theme->get('Tags'),
            'isAllowed'    => $theme->is_allowed(),
            'isActive'     => (is_multisite() ? array() : false),
            'defaultTheme' => (defined('WP_DEFAULT_THEME') && WP_DEFAULT_THEME == $slug),
        );
    }

    private function the_brand_setup($id)
    {
        // initialize brand
        $brand = DUP_PRO_Brand_Entity::get_by_id((int) $id);

        // Prepare default fields
        $brand_property_default = array(
            'name'      => 'Duplicator Professional',
            'isDefault' => true,
            'logo'      => '',
            'enabled'   => false,
            'style'     => array()
        );

        // Returns property
        $brand_property = array();

        // Is default brand selected?
        $brand_property['isDefault'] = $id === DUP_PRO_BRAND_IDS::defaultBrand;

        // Set brand name
        $brand_property['name'] = $brand_property['isDefault'] ? 'Duplicator Professional' : $brand->name;

        // Set logo and hosted images path
        if (isset($brand->logo)) {
            $brand_property['logo'] = $brand->logo;
            // Find images
            preg_match_all('/<img.*?src="([^"]+)".*?>/', $brand->logo, $arr_img, PREG_PATTERN_ORDER);

            // https://regex101.com/r/eEyf5S/2
            // Fix hosted image url path
            if (isset($arr_img[1]) && count($brand->attachments) > 0 && count($arr_img[1]) === count($brand->attachments)) {
                foreach ($arr_img[1] as $i => $find) {
                    $brand_property['logo'] = str_replace($find, 'assets/images/brand' . $brand->attachments[$i], $brand_property['logo']);
                }
            }
            $brand_property['logo'] = stripslashes($brand_property['logo']);
        }

        // Set is enabled
        if (!empty($brand_property['logo']) && isset($brand->active) && $brand->active) {
            $brand_property['enabled'] = true;
        }

        // Let's include style
        if (isset($brand->style)) {
            $brand_property['style'] = $brand->style;
        }

        // Merge data properly
        if (function_exists("array_replace") && version_compare(phpversion(), '5.3.0', '>=')) {
            $brand_property = array_replace($brand_property_default, $brand_property);
        } else {
            $brand_property = array_merge($brand_property_default, $brand_property);
        }

        return $brand_property;
    }

    /**
     *
     * @return string
     */
    public function getArchiveFullPath()
    {
        return SnapIO::safePath($this->Package->StorePath) . '/' . $this->Package->Archive->File;
    }

    /**
     *  createZipBackup
     *  Puts an installer zip file in the archive for backup purposes.
     */
    private function add_extra_files()
    {
        $success          = false;
        $archive_filepath = SnapIO::safePath("{$this->Package->StorePath}/{$this->Package->Archive->File}");

        $this->initConfigFiles();
        $this->createManualExtractCheckFile();

        if ($this->Package->Archive->file_count != 2) {
            DUP_PRO_LOG::trace("Doing archive file check");
            // Only way it's 2 is if the root was part of the filter in which case the archive won't be there
            if (file_exists($archive_filepath) == false) {
                $error_text    = sprintf(DUP_PRO_U::__("Zip archive %1s not present."), $archive_filepath);
                $fix_text      = DUP_PRO_U::__("Click on button to set archive engine to DupArchive.");
                DUP_PRO_Log::error("$error_text. **RECOMMENDATION: $fix_text", '', false);
                $system_global = DUP_PRO_System_Global_Entity::get_instance();
                $system_global->add_recommended_quick_fix(
                    $error_text,
                    $fix_text,
                    array(
                        'global' => array(
                            'archive_build_mode' => 3
                        )
                    )
                );
                $system_global->save();
                return false;
            }
        }

        DUP_PRO_LOG::trace("Add extra files: Current build mode = " . $this->Package->build_progress->current_build_mode);
        if ($this->Package->build_progress->current_build_mode == DUP_PRO_Archive_Build_Mode::ZipArchive) {
            $success = $this->zipArchiveAddExtra();
        } elseif ($this->Package->build_progress->current_build_mode == DUP_PRO_Archive_Build_Mode::Shell_Exec) {
            // Adding the shellexec fail text fix
            if (($success = $this->shellZipAddExtra()) == false) {
                $error_text    = DUP_PRO_U::__("Problem adding installer to archive");
                $fix_text      = DUP_PRO_U::__("Click on button to set archive engine to DupArchive.");
                $system_global = DUP_PRO_System_Global_Entity::get_instance();
                $system_global->add_recommended_quick_fix(
                    $error_text,
                    $fix_text,
                    array(
                        'global' => array(
                            'archive_build_mode' => 3
                        )
                    )
                );
                $system_global->save();
            }
        } elseif ($this->Package->build_progress->current_build_mode == DUP_PRO_Archive_Build_Mode::DupArchive) {
            $success = $this->dupArchiveAddExtra();
        }

        try {
            $archive_config_filepath = SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH_TMP) . "/{$this->Package->NameHash}_archive.txt";
            // No sense keeping these files
            @unlink($archive_config_filepath);
            $this->origFileManger->deleteMainFolder();
            $this->deleteManualExtractCheckFile();
        } catch (Exception $e) {
            DUP_PRO_Log::infoTrace("Error clean temp installer file, but continue. Message: " . $e->getMessage());
        }

        $this->Package->Archive->Size = @filesize($archive_filepath);
        return $success;
    }

    public function getInstallerBackupName()
    {
        return $this->Package->NameHash . '_' . DUP_PRO_Global_Entity::get_instance()->get_installer_backup_filename();
    }

    private function dupArchiveAddExtra()
    {

        $logger = new DUP_PRO_Dup_Archive_Logger();
        DupArchiveEngine::init($logger, null);

        $archivePath = $this->getArchiveFullPath();
        $extraPoistion = filesize($archivePath);

        foreach ($this->getExtraFilesLists() as $extraItem) {
            if (is_dir($extraItem['sourcePath'])) {
                $basePath = dirname($extraItem['sourcePath']);
                $destPath = ltrim(trailingslashit($extraItem['archivePath']), '\\/');
                $result   = DupArchiveEngine::addDirectoryToArchiveST($archivePath, $extraItem['sourcePath'], $basePath, true, $destPath);

                $this->numFilesAdded += $result->numFilesAdded;
                $this->numDirsAdded  += $result->numDirsAdded;
            } else {
                DupArchiveEngine::addRelativeFileToArchiveST($archivePath, $extraItem['sourcePath'], $extraItem['archivePath']);
                $this->numFilesAdded++;
            }
        }

        // store extra files position
        $src = json_encode(array(DupArchiveEngine::EXTRA_FILES_POS_KEY => $extraPoistion));
        $src .= str_repeat("\0", DupArchiveEngine::INDEX_FILE_SIZE - strlen($src));
        DupArchiveEngine::replaceFileContent($archivePath, $src, DupArchiveEngine::INDEX_FILE_NAME, 0, 3000);

        return true;
    }

    /**
     *
     * @return boolean
     * @throws \Exception
     */
    private function zipArchiveAddExtra()
    {
        $zipArchive   = new ZipArchive();
        $isCompressed = $this->Package->build_progress->current_build_compression;

        if ($zipArchive->open($this->getArchiveFullPath(), ZipArchive::CREATE) !== true) {
            throw new \Exception("Couldn't open zip archive ");
        }

        DUP_PRO_LOG::trace("Successfully opened zip");

        foreach ($this->getExtraFilesLists() as $extraItem) {
            if (is_dir($extraItem['sourcePath'])) {
                if (!DUP_PRO_Zip_U::addDirWithZipArchive($zipArchive, $extraItem['sourcePath'], true, $extraItem['archivePath'], $isCompressed)) {
                    throw new \Exception('INSTALLER FILES: zip add ' . $extraItem['label'] . ' folder error on folder ' . $extraItem['sourcePath']);
                }
            } else {
                if (!DUP_PRO_Zip_U::addFileToZipArchive($zipArchive, $extraItem['sourcePath'], $extraItem['archivePath'], $isCompressed)) {
                    throw new \Exception('INSTALLER FILES: zip add ' . $extraItem['label'] . ' file error on file ' . $extraItem['sourcePath']);
                }
            }
        }

        if ($zipArchive->close() === false) {
            throw new \Exception("Couldn't close zip archive ");
        }

        DUP_PRO_LOG::trace('After ziparchive close when adding installer');

        $this->zipArchiveCheck();
        return true;
    }

    private function zipArchiveCheck()
    {
        /* ------ ZIP CONSISTENCY CHECK ------ */
        DUP_PRO_LOG::trace("Running ZipArchive consistency check");
        $zip = new ZipArchive();

        // ZipArchive::CHECKCONS will enforce additional consistency checks
        $res = $zip->open($this->getArchiveFullPath(), ZipArchive::CHECKCONS);
        if ($res !== true) {
            $consistency_error = sprintf(DUP_PRO_U::__('ERROR: Cannot open created archive. Error code = %1$s'), $res);
            DUP_PRO_LOG::trace($consistency_error);
            switch ($res) {
                case ZipArchive::ER_NOZIP:
                    $consistency_error = DUP_PRO_U::__('ERROR: Archive is not valid zip archive.');
                    break;
                case ZipArchive::ER_INCONS:
                    $consistency_error = DUP_PRO_U::__("ERROR: Archive doesn't pass consistency check.");
                    break;
                case ZipArchive::ER_CRC:
                    $consistency_error = DUP_PRO_U::__("ERROR: Archive checksum is bad.");
                    break;
            }

            throw new \Exception($consistency_error);
        }

        $failed = false;
        foreach ($this->getInstallerPathsForIntegrityCheck() as $path) {
            if ($zip->locateName($path) === false) {
                $failed = true;
                DUP_PRO_Log::infoTrace(DUP_PRO_U::__("Couldn't find $path in archive"));
            }
        }

        if ($failed) {
            DUP_PRO_Log::info(DUP_PRO_U::__('ARCHIVE CONSISTENCY TEST: FAIL'));
            throw new \Exception("Zip for package " . $this->Package->ID . " didn't passed consistency test");
        } else {
            DUP_PRO_Log::info(DUP_PRO_U::__('ARCHIVE CONSISTENCY TEST: PASS'));
            DUP_PRO_LOG::trace("Zip for package " . $this->Package->ID . " passed consistency test");
        }

        $zip->close();
    }

    /**
     *
     * @return boolean
     * @throws \Exception
     */
    private function shellZipAddExtra()
    {
        $isCompressed   = $this->Package->build_progress->current_build_compression;
        $tmpExtraFolder = SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH_TMP) . '/extras/';

        if (file_exists($tmpExtraFolder)) {
            if (SnapIO::rrmdir($tmpExtraFolder) === false) {
                throw new \Exception("Error deleting $tmpExtraFolder");
            }
        }

        /**
          if (!wp_mkdir_p($tmpDupInstallerPath)) {
          throw new \Exception("Error creating extras directory, Couldn't create $tmpDupInstallerPath");
          }* */
        foreach ($this->getExtraFilesLists() as $extraItem) {
            if (is_dir($extraItem['sourcePath'])) {
                $destPath = $tmpExtraFolder . trailingslashit($extraItem['archivePath']) . basename($extraItem['sourcePath']);
            } else {
                $destPath = $tmpExtraFolder . $extraItem['archivePath'];
            }

            if (!wp_mkdir_p(dirname($destPath))) {
                throw new \Exception("Error creating extras directory, Couldn't create " . dirname($destPath));
            }

            if (!SnapIO::rcopy($extraItem['sourcePath'], $destPath)) {
                throw new \Exception("Error copy " . $extraItem['sourcePath'] . ' to ' . $destPath);
            }
        }

        //-- STAGE 1 ADD
        $compression_parameter = DUP_PRO_Shell_U::getCompressionParam($isCompressed);
        $command               = 'cd ' . escapeshellarg(SnapIO::safePath($tmpExtraFolder));
        $command               .= ' && ' . escapeshellcmd(DUP_PRO_Zip_U::getShellExecZipPath()) . " $compression_parameter" . ' -g -rq ';
        $command               .= escapeshellarg($this->getArchiveFullPath()) . ' ./* ./.[^.]*';
        DUP_PRO_LOG::trace("Executing Shell Exec Zip Stage 1 to add extras: $command");
        if (($stderr                = shell_exec($command)) != '') {
            throw new \Exception("Error excecuting shell command: " . $command . ' MSG: ' . $stderr);
        }

        $this->shellZipFilesCheck();

        if (!SnapIO::rrmdir($tmpExtraFolder)) {
            DUP_PRO_LOG::trace("Couldn't recursively delete {$tmpExtraFolder}");
        }
        return true;
    }

    /**
     *
     * @return boolean
     * @throws \Exception
     */
    private function shellZipFilesCheck()
    {
        if (DUP_PRO_U::getExeFilepath('unzip') == null) {
            DUP_PRO_LOG::trace("unzip doesn't exist so not doing the extra file check");
            return;
        }
        $filesToValidate = $this->getInstallerPathsForIntegrityCheck();
        DUP_PRO_Log::infoTrace('CHECK FILES ' . \Duplicator\Libs\Snap\SnapLog::v2str($filesToValidate));

        // Verify the essential extras got in there
        $extraCountString = "unzip -Z1 '" . $this->getArchiveFullPath() . "' | grep '^\(" . implode("\|", $filesToValidate) . "\)' | wc -l";
        DUP_PRO_LOG::info("Executing extra count string $extraCountString");
        $extraCount       = DUP_PRO_Shell_U::runAndGetResponse($extraCountString, 1);
        if (is_numeric($extraCount)) {
            // Accounting for the sql and installer back files
            if ($extraCount != count($filesToValidate)) {
                throw new \Exception("Tried to verify core extra files but one or more were missing. Count = $extraCount");
            }
        } else {
            throw new \Exception("Error retrieving extra count in shell zip " . $extraCount);
        }

        DUP_PRO_LOG::trace("Core extra files confirmed to be in the archive");
        return true;
    }

    /**
     * Creates the original_files_ folder in the tmp directory where all config files are saved
     * to be later added to the archives
     *
     * @throws Exception
     */
    public function initConfigFiles()
    {
        $this->origFileManger->init();
        $configFilePaths = $this->getConfigFilePaths();
        foreach ($configFilePaths as $identifier => $path) {
            if ($path !== false) {
                try {
                    $this->origFileManger->addEntry($identifier, $path, SnapOrigFileManager::MODE_COPY, self::CONFIG_ORIG_FILE_FOLDER_PREFIX . $identifier);
                } catch (Exception $ex) {
                    DUP_PRO_Log::infoTrace("Error while handling config files: " . $ex->getMessage());
                }
            }
        }

        //Clean sensitive information from wp-config.php file.
        self::cleanTempWPConfArkFilePath($this->origFileManger->getEntryStoredPath(self::CONFIG_ORIG_FILE_WPCONFIG_ID));
    }

    /**
     * Gets config files path
     *
     * @return string[] array of config files in identifier => path format
     */
    public function getConfigFilePaths()
    {
        $home        = DUP_PRO_Archive::getArchiveListPaths('home');
        $configFiles = array(
            self::CONFIG_ORIG_FILE_USERINI_ID   => $home . '/.user.ini',
            self::CONFIG_ORIG_FILE_PHPINI_ID    => $home . '/php.ini',
            self::CONFIG_ORIG_FILE_WEBCONFIG_ID => $home . '/web.config',
            self::CONFIG_ORIG_FILE_HTACCESS_ID  => $home . '/.htaccess',
            self::CONFIG_ORIG_FILE_WPCONFIG_ID  => DUP_PRO_Archive::getWPConfigFilePath()
        );
        foreach ($configFiles as $identifier => $path) {
            if (!file_exists($path)) {
                unset($configFiles[$identifier]);
            }
        }

        return $configFiles;
    }

    public function getInstallerPathsForIntegrityCheck()
    {
        $filesToValidate = array(
            'dup-installer/api/class.api.php',
            'dup-installer/assets/index.php',
            'dup-installer/classes/index.php',
            'dup-installer/ctrls/index.php',
            'dup-installer/src/Utils/Autoloader.php',
            'dup-installer/templates/default/page-help.php',
            'dup-installer/main.installer.php',
        );

        foreach ($this->getExtraFilesLists() as $extraItem) {
            if (is_file($extraItem['sourcePath'])) {
                $filesToValidate[] = $extraItem['archivePath'];
            } else {
                if (file_exists(trailingslashit($extraItem['sourcePath']) . '/index.php')) {
                    $filesToValidate[] = ltrim(trailingslashit($extraItem['archivePath']), '\\/') . basename($extraItem['sourcePath']) . '/index.php';
                } else {
                    // SKIP CHECK
                }
            }
        }

        return array_unique($filesToValidate);
    }

    private function createManualExtractCheckFile()
    {
        $file_path = $this->getManualExtractFilePath();
        return SnapIO::filePutContents($file_path, '');
    }

    private function getManualExtractFilePath()
    {
        $tmp = SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH_TMP);
        return $tmp . '/dup-manual-extract__' . $this->Package->get_package_hash();
    }

    private function getEmbeddedManualExtractFilePath()
    {
        $embedded_filepath = 'dup-installer/dup-manual-extract__' . $this->Package->get_package_hash();
        return $embedded_filepath;
    }

    private function deleteManualExtractCheckFile()
    {
        SnapIO::rm($this->getManualExtractFilePath());
    }

    /**
     * Clear out sensitive database connection information
     *
     * @param $temp_conf_ark_file_path Temp config file path
     * @throws Exception
     */
    private static function cleanTempWPConfArkFilePath($temp_conf_ark_file_path)
    {
        try {
            if (function_exists('token_get_all')) {
                require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/lib/config/class.wp.config.tranformer.php');
                $transformer = new DupProWPConfigTransformer($temp_conf_ark_file_path);
                $constants   = array('DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST');
                foreach ($constants as $constant) {
                    if ($transformer->exists('constant', $constant)) {
                        $transformer->update('constant', $constant, '');
                    }
                }
            }
        } catch (Exception $e) {
            DUP_PRO_LOG::infoTrace("Can\'t inizialize wp-config transformer Message: " . $e->getMessage());
        } catch (Error $e) {
            DUP_PRO_LOG::infoTrace("Can\'t inizialize wp-config transformer Message: " . $e->getMessage());
        }
    }

    private function getEmbeddedScanFileList()
    {
        return 'dup-installer/dup-scanned-files__' . $this->Package->get_package_hash() . '.txt';
    }

    private function getEmbeddedScanDirList()
    {
        return 'dup-installer/dup-scanned-dirs__' . $this->Package->get_package_hash() . '.txt';
    }

    /**
     * Get scan.json file path along with name in archive file
     */
    private function getEmbeddedScanFilePath()
    {
        return 'dup-installer/dup-scan__' . $this->Package->get_package_hash() . '.json';
    }

    /**
     * Get archive.txt file path along with name in archive file
     */
    private function getArchiveTxtFilePath()
    {
        return 'dup-installer/dup-archive__' . $this->Package->get_package_hash() . '.txt';
    }

    /**
     * Get archive.txt file path along with name in archive file
     */
    private function getEmbeddedSqlFile()
    {
        return 'dup-installer/dup-database__' . $this->Package->get_package_hash() . '.sql';
    }

    /**
     * Get scanned_files.txt file path along with name in archive file
     *
     * @return string scanned_files.txt file path
     */
    private function getEmbeddedFileListFilePath()
    {
        return 'dup-installer/dup-scanned-files__' . $this->Package->get_package_hash() . '.txt';
    }

    /**
     * Get scanned_dirs.txt file path along with name in archive file
     *
     * @return string scanned_dirs.txt file path
     */
    private function getEmbeddedDirListFilePath()
    {
        return 'dup-installer/dup-scanned-dirs__' . $this->Package->get_package_hash() . '.txt';
    }
}
