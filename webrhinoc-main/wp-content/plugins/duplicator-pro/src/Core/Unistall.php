<?php

/**
 * Interface that collects the functions of initial duplicator Bootstrap
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Core;

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Utils\ExpireOptions;

class Unistall
{

    /**
     * Registrer unistall hoosk
     *
     * @return void
     */
    public static function registreHooks()
    {
        if (is_admin()) {
            register_deactivation_hook(DUPLICATOR____FILE, array(__CLASS__, 'deactivate'));
            register_uninstall_hook(DUPLICATOR____FILE, array(__CLASS__, 'unistall'));
            add_action('duplicator_unistall', array(__CLASS__, 'removePackages'), 15);
            add_action('duplicator_unistall', array(__CLASS__, 'removeSettings'), 15);
            add_action('duplicator_unistall', array(__CLASS__, 'removePluginVersion'), 20);
        }
    }

    /**
     * Deactivation Hook:
     * Hooked into `register_deactivation_hook`.  Routines used to deactivate the plugin
     * For uninstall see uninstall.php  WordPress by default will call the uninstall.php file
     *
     * @return null
     */
    public static function deactivate()
    {
        \DUP_PRO_Migration::renameInstallersPhpFiles();

        //Logic has been added to uninstall.php
        //Force recalculation of next run time on activation
        //see the function \DUP_PRO_Package_Runner::calculate_earliest_schedule_run_time()
        \DUP_PRO_Log::trace("Resetting next run time for active schedules");
        $activeSchedules = \DUP_PRO_Schedule_Entity::get_active();
        ExpireOptions::deleteAll();
        foreach ($activeSchedules as $activeSchedule) {
            $activeSchedule->next_run_time = -1;
            $activeSchedule->save();
        }
    }

    /**
     * Unistall function
     *
     * @return void
     */
    public static function unistall()
    {
        \DUP_PRO_Migration::renameInstallersPhpFiles();

        do_action('duplicator_unistall');
    }

    /**
     * Remove plugin option version
     *
     * @return void
     */
    public static function removePluginVersion()
    {
        delete_option(\DUP_PRO_Plugin_Upgrade::DUP_VERSION_OPT_KEY);
    }

    /**
     * Remove all packages
     *
     * @return void
     */
    public static function removePackages()
    {
        $global = \DUP_PRO_Global_Entity::get_instance();

        if (!$global->uninstall_packages) {
            return;
        }

        $tableName = $GLOBALS['wpdb']->base_prefix . 'duplicator_pro_packages';
        $GLOBALS['wpdb']->query('DROP TABLE IF EXISTS ' . $tableName);

        $ssdir = SnapIO::safePath(DUPLICATOR_PRO_SSDIR_PATH);

        //Sanity check for strange setup
        $check = glob("{$ssdir}/wp-config.php");
        if (count($check) == 0) {
            SnapIO::rrmdir($ssdir, true);
        }
    }

    /**
     * Remove plugins settings
     *
     * @return void
     */
    public static function removeSettings()
    {
        $global = \DUP_PRO_Global_Entity::get_instance();

        if (!$global->uninstall_settings) {
            return;
        }

        $tableName = $GLOBALS['wpdb']->base_prefix . \DUP_PRO_JSON_Entity_Base::DEFAULT_TABLE_NAME;
        $GLOBALS['wpdb']->query('DROP TABLE IF EXISTS ' . $tableName);

        $optionsTableName = $GLOBALS['wpdb']->base_prefix . "options";
        $dupOptionNames   = $GLOBALS['wpdb']->get_col("SELECT `option_name` FROM `{$optionsTableName}` WHERE `option_name` REGEXP '^duplicator_pro_'");

        foreach ($dupOptionNames as $dupOptionName) {
            delete_option($dupOptionName);
        }

        ExpireOptions::deleteAll();

        $dupOptionTransientNames = $GLOBALS['wpdb']->get_col(
            "SELECT `option_name` FROM `{$optionsTableName}` WHERE `option_name` REGEXP '^_transient_duplicator_pro'"
        );

        foreach ($dupOptionTransientNames as $dupOptionTransientName) {
            delete_transient(str_replace("_transient_", "", $dupOptionTransientName));
        }
    }
}
