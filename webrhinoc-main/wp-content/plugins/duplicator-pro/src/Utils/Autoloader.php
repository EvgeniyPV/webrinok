<?php

/**
 * Auloader calsses
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Utils;

/**
 * Autoloader calss, dont user Duplicator library here
 */
final class Autoloader
{
    const ROOT_NAMESPACE                 = 'Duplicator\\';
    const ROOT_INSTALLER_NAMESPACE       = 'Duplicator\\Installer\\';
    const ROOT_ADDON_NAMESPACE           = 'Duplicator\\Addons\\';
    const ROOT_ADDON_INSTALLER_NAMESPACE = 'Duplicator\\Installer\\Addons\\';

    protected static $nameSpacesMapping = null;

    /**
     * Register autoloader function
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register(array(__CLASS__, 'load'));
    }

    /**
     * Load class
     *
     * @param string $className class name
     *
     * @return boolean
     */
    public static function load($className)
    {
        // @todo remove legacy logic in autoloading when duplicator is fully converted.
        if (strpos($className, self::ROOT_NAMESPACE) !== 0) {
            $legacyMappging = self::customLegacyMapping();
            $legacyClass    = strtolower(ltrim($className, '\\'));
            if (array_key_exists($legacyClass, $legacyMappging)) {
                if (file_exists($legacyMappging[$legacyClass])) {
                    include_once($legacyMappging[$legacyClass]);
                    return true;
                }
            }
        } else {
            if (($filepath = self::getAddonFile($className)) === false) {
                foreach (self::getNamespacesMapping() as $namespace => $mappedPath) {
                    if (strpos($className, $namespace) !== 0) {
                        continue;
                    }

                    $filepath = $mappedPath . str_replace('\\', '/', substr($className, strlen($namespace))) . '.php';
                    if (file_exists($filepath)) {
                        include_once($filepath);
                        return true;
                    }
                }
            } else {
                if (file_exists($filepath)) {
                    include_once($filepath);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return addon file by class
     *
     * @param string $class class name
     *
     * @return string
     */
    protected static function getAddonFile($class)
    {
        $matches = null;
        if (preg_match('/^\\\\?Duplicator(?:\\\\Installer)?\\\\Addons\\\\(.+?)\\\\(.+)$/', $class, $matches) !== 1) {
            return false;
        }

        $addonName = $matches[1];
        $subClass  = $matches[2];
        $basePath  = DUPLICATOR____PATH . '/addons/' . strtolower($addonName) . '/';

        if (strpos($class, self::ROOT_ADDON_INSTALLER_NAMESPACE) === 0) {
            $basePath .= 'installer/' . strtolower($addonName) . '/';
        }

        if (self::endsWith($class, $addonName) === false) {
            $basePath .= 'src/';
        }

        return $basePath . str_replace('\\', '/', $subClass) . '.php';
    }

    /**
     * mappgin of some legacy classes
     *
     * @return array
     */
    protected static function customLegacyMapping()
    {
        return array(
            'dup_pro_u'                       => DUPLICATOR____PATH . '/classes/utilities/class.u.php',
            'dup_pro_str'                     => DUPLICATOR____PATH . '/classes/utilities/class.u.string.php',
            'dup_pro_date'                    => DUPLICATOR____PATH . '/classes/utilities/class.u.date.php',
            'dup_pro_zip_u'                   => DUPLICATOR____PATH . '/classes/utilities/class.u.zip.php',
            'dup_pro_upgrade_u'               => DUPLICATOR____PATH . '/classes/utilities/class.u.upgrade.php',
            'dup_pro_validator'               => DUPLICATOR____PATH . '/classes/utilities/class.u.validator.php',
            'dup_pro_tree_files'              => DUPLICATOR____PATH . '/classes/utilities/class.u.tree.files.php',
            'dup_pro_wp_u'                    => DUPLICATOR____PATH . '/classes/utilities/class.u.wp.php',
            'dup_pro_mu'                      => DUPLICATOR____PATH . '/classes/utilities/class.u.multisite.php',
            'dup_pro_json_u'                  => DUPLICATOR____PATH . '/classes/utilities/class.u.json.php',
            'dup_pro_migration'               => DUPLICATOR____PATH . '/classes/utilities/class.u.migration.php',
            'dup_pro_low_u'                   => DUPLICATOR____PATH . '/classes/utilities/class.u.low.php',
            'dup_pro_settings_u'              => DUPLICATOR____PATH . '/classes/utilities/class.u.settings.php',
            'dup_pro_json_entity_base'        => DUPLICATOR____PATH . '/classes/entities/class.json.entity.base.php',
            'dup_pro_system_global_entity'    => DUPLICATOR____PATH . '/classes/entities/class.system.global.entity.php',
            'dup_pro_storage_entity'          => DUPLICATOR____PATH . '/classes/entities/class.storage.entity.php',
            'dup_pro_profile_logs_entity'     => DUPLICATOR____PATH . '/classes/entities/class.profilelogs.entity.php',
            'dup_pro_global_entity'           => DUPLICATOR____PATH . '/classes/entities/class.global.entity.php',
            'dup_pro_package_template_entity' => DUPLICATOR____PATH . '/classes/entities/class.package.template.entity.php',
            'dup_pro_schedule_entity'         => DUPLICATOR____PATH . '/classes/entities/class.schedule.entity.php',
            'dup_pro_duparchive_expand_state_entity' => DUPLICATOR____PATH . '/classes/entities/class.duparchive.expandstate.entity.php',
            'dup_pro_schedule_repeat_types'   => DUPLICATOR____PATH . '/classes/entities/class.schedule.entity.php',
            'dup_pro_schedule_days'           => DUPLICATOR____PATH . '/classes/entities/class.schedule.entity.php',
            'dup_pro_verifier_base'           => DUPLICATOR____PATH . '/classes/entities/class.verifiers.php',
            'dup_pro_required_verifier'       => DUPLICATOR____PATH . '/classes/entities/class.verifiers.php',
            'dup_pro_range_verifier'          => DUPLICATOR____PATH . '/classes/entities/class.verifiers.php',
            'dup_pro_length_verifier'         => DUPLICATOR____PATH . '/classes/entities/class.verifiers.php',
            'dup_pro_regex_verifier'          => DUPLICATOR____PATH . '/classes/entities/class.verifiers.php',
            'dup_pro_email_verifier'          => DUPLICATOR____PATH . '/classes/entities/class.verifiers.php',
            'dup_pro_package_runner'          => DUPLICATOR____PATH . '/classes/package/class.pack.runner.php',
            'dup_pro_package'                 => DUPLICATOR____PATH . '/classes/package/class.pack.php',
            'dup_pro_package_importer'        => DUPLICATOR____PATH . '/classes/package/class.pack.importer.php',
            'dup_pro_package_recover'         => DUPLICATOR____PATH . '/classes/package/class.pack.recover.php',
            'dup_pro_archive'                 => DUPLICATOR____PATH . '/classes/package/class.pack.archive.php',
            'dup_pro_database'                => DUPLICATOR____PATH . '/classes/package/class.pack.database.php',
            'dup_pro_dup_archive_create_state' => DUPLICATOR____PATH . '/classes/package/duparchive/class.pack.archive.duparchive.state.create.php',
            'dup_pro_duparchive_expand_state' => DUPLICATOR____PATH . '/classes/package/duparchive/class.pack.archive.duparchive.state.expand.php',
            'dup_pro_dup_archive'             => DUPLICATOR____PATH . '/classes/package/duparchive/class.pack.archive.duparchive.php',
            'dup_pro_dup_archive_logger'      => DUPLICATOR____PATH . '/classes/package/duparchive/class.pack.archive.duparchive.php',
            'dup_pro_custom_host_manager'     => DUPLICATOR____PATH . '/classes/host/class.custom.host.manager.php',
            'dup_pro_ui'                      => DUPLICATOR____PATH . '/classes/ui/class.ui.php',
            'dup_pro_ui_alert'                => DUPLICATOR____PATH . '/classes/ui/class.ui.alert.php',
            'dup_pro_ui_viewstate'            => DUPLICATOR____PATH . '/classes/ui/class.ui.viewstate.php',
            'dup_pro_ui_dialog'               => DUPLICATOR____PATH . '/classes/ui/class.ui.dialog.php',
            'dup_pro_ui_notice'               => DUPLICATOR____PATH . '/classes/ui/class.ui.notice.php',
            'dup_pro_ui_messages'             => DUPLICATOR____PATH . '/classes/ui/class.ui.messages.php',
            'dup_pro_ui_screen'               => DUPLICATOR____PATH . '/classes/ui/class.ui.screen.base.php',
            'dup_pro_crypt'                   => DUPLICATOR____PATH . '/classes/class.crypt.custom.php',
            'dup_pro_crypt_blowfish'          => DUPLICATOR____PATH . '/classes/class.crypt.blowfish.php',
            'dup_pro_php_log'                 => DUPLICATOR____PATH . '/classes/class.php.log.php',
            'dup_pro_constants'               => DUPLICATOR____PATH . '/classes/class.constants.php',
            'dup_pro_db'                      => DUPLICATOR____PATH . '/classes/class.db.php',
            'dup_pro_plugin_upgrade'          => DUPLICATOR____PATH . '/classes/class.plugin.upgrade.php',
            'dup_pro_log'                     => DUPLICATOR____PATH . '/classes/class.logging.php',
            'dup_pro_restoreonly_package'     => DUPLICATOR____PATH . '/classes/class.restoreonly.package.php',
            'dup_pro_server'                  => DUPLICATOR____PATH . '/classes/class.server.php',
            'dup_pro_web_services'            => DUPLICATOR____PATH . '/ctrls/class.web.services.php',
            'dup_pro_ctrl_schedule'           => DUPLICATOR____PATH . '/ctrls/ctrl.schedule.php',
            'dup_pro_ctrl_package'            => DUPLICATOR____PATH . '/ctrls/ctrl.package.php',
            'dup_pro_ctrl_tools'              => DUPLICATOR____PATH . '/ctrls/ctrl.tools.php',
            'dup_pro_ctrl_import'             => DUPLICATOR____PATH . '/ctrls/ctrl.import.php',
            'dup_pro_ctrl_recovery'           => DUPLICATOR____PATH . '/ctrls/ctrl.recovery.php',
            'dup_pro_package_screen'          => DUPLICATOR____PATH . '/views/packages/screen.php'
        );
    }

    /**
     * Return namespace mapping
     *
     * @return string[]
     */
    protected static function getNamespacesMapping()
    {
        // the order is important, it is necessary to insert the longest namespaces first
        return array(
            self::ROOT_INSTALLER_NAMESPACE => DUPLICATOR____PATH . '/installer/dup-installer/src/',
            self::ROOT_NAMESPACE           => DUPLICATOR____PATH . '/src/'
        );
    }

    /**
     * Returns true if the $haystack string end with the $needle, only for internal use
     *
     * @param string $haystack The full string to search in
     * @param string $needle   The string to for
     *
     * @return bool Returns true if the $haystack string starts with the $needle
     */
    protected static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}
