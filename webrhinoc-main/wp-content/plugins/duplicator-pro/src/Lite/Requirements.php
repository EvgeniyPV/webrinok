<?php

/**
 * Class that collects the functions of initial checks on the requirements to run the plugin
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Lite;

class Requirements implements \Duplicator\Core\RequirementsInterface
{
    const DUP_PRO_PLUGIN_KEY = 'duplicator-pro/duplicator-pro.php';

    /**
     *
     * @var string // curent plugin file full path
     */
    protected static $pluginFile = '';

    /**
     *
     * @var string // message on deactivation
     */
    protected static $deactivationMessage = '';

    /**
     * This function checks the requirements to run Duplicator.
     * At this point wordpress is not yet completely initialized so functionality is limited.
     * It need to hook into "admin_init" to get the full functionality of wordpress.
     *
     * @param string $pluginFile main plugin file path
     *
     * @return boolean true if plugin can be exectued
     */
    public static function canRun($pluginFile)
    {
        $result           = true;
        self::$pluginFile = $pluginFile;

        if ($result === true && is_multisite()) {
            /* Deactivation of the plugin disabled in favor of a notification for the next version
             * Uncomment this to enable the logic.
             *
              add_action('admin_init', array(__CLASS__, 'addMultisiteNotice'));
              self::$deactivationMessage = __('Can\'t enable Duplicator LITE in a multisite installation', 'duplicator');
              $result                    = false;
             */


            // TEMP WARNING NOTICE, remove this when the deactiovation logic is enable
            add_action('admin_init', array(__CLASS__, 'addTempWarningMultisiteNotice'));
        }


        if ($result === true && self::isPluginActive(self::DUP_PRO_PLUGIN_KEY)) {
            /* Deactivation of the plugin disabled in favor of a notification for the next version
             * Uncomment this to enable the logic.
             *
              add_action('admin_init', array(__CLASS__, 'addProEnableNotice'));
              self::$deactivationMessage = __('Can\'t enable Duplicator LITE if the PRO version is enabled', 'duplicator');
              $result                    = false;
             */

            // TEMP WARNING NOTICE, remove this when the deactiovation logic is enable
            add_action('admin_init', array(__CLASS__, 'addTempWarningProEnableNotice'));
        }

        if ($result === false) {
            register_activation_hook($pluginFile, array(__CLASS__, 'deactivateOnActivation'));
        }

        return $result;
    }

    /**
     *
     * @return string
     */
    public static function getAddsHash()
    {
        return '7b2272223a5b224c69746542617365225d2c226664223a5b2250726f42617365225d7d';
    }

    /**
     * Check if plugin is active
     *
     * @param string $plugin plugin slug
     *
     * @return boolean return strue if plugin key is active and plugin file exists
     */
    protected static function isPluginActive($plugin)
    {
        $isActive = false;
        if (in_array($plugin, (array) get_option('active_plugins', array()))) {
            $isActive = true;
        }

        if (is_multisite()) {
            $plugins = get_site_option('active_sitewide_plugins');
            if (isset($plugins[$plugin])) {
                $isActive = true;
            }
        }

        return ($isActive && file_exists(WP_PLUGIN_DIR . '/' . $plugin));
    }

    /**
     * Display admin notice only if user can manage plugins.
     *
     * @return void
     */
    public static function addProEnableNotice()
    {
        if (current_user_can('activate_plugins')) {
            add_action('admin_notices', array(__CLASS__, 'proEnabledNotice'));
        }
    }

    /**
     * Display admin notice
     *
     * @return void
     */
    public static function addMultisiteNotice()
    {
        if (current_user_can('activate_plugins')) {
            add_action('admin_notices', array(__CLASS__, 'multisiteNotice'));
        }
    }

    /**
     * Display admin notice only if user can manage plugins.
     *
     * @return void
     */
    public static function addTempWarningProEnableNotice()
    {
        if (current_user_can('activate_plugins')) {
            add_action('admin_notices', array(__CLASS__, 'tempWarningProEnableNotice'));
        }
    }

    /**
     * Mutisite notice warning
     *
     * @return void
     */
    public static function addTempWarningMultisiteNotice()
    {
        if (current_user_can('activate_plugins')) {
            add_action('admin_notices', array(__CLASS__, 'tempWarningMultisiteNotice'));
        }
    }

    /**
     * Deactivate current plugin on activation
     *
     * @return void
     */
    public static function deactivateOnActivation()
    {
        deactivate_plugins(plugin_basename(self::$pluginFile));
        wp_die(self::$deactivationMessage);
    }

    /**
     * Display admin notice if duplicator pro is enabled
     *
     * @return void
     */
    public static function proEnabledNotice()
    {
        ?>
        <div class="error notice">
            <p>
        <?php echo 'DUPLICATOR LITE: ' . __('Duplicator LITE cannot be work if Duplicator PRO is active.', 'duplicator'); ?>
            </p>
            <p>
        <?php echo __('If you want to use Duplicator LITE you must first deactivate the PRO version', 'duplicator'); ?><br>
                <b>
                <?php echo __('Please disable the LITE version if it is not needed.', 'duplicator'); ?>
                </b>
            </p>
        </div>
        <?php
    }

    /**
     * Display admin notice if duplicator pro is enabled
     *
     * @return void
     */
    public static function multisiteNotice()
    {
        ?>
        <div class="error notice">
            <p>
        <?php echo 'DUPLICATOR LITE: ' . __('Duplicator LITE can\'t work on multisite installation.', 'duplicator'); ?>
            </p>
            <p>
        <?php echo __('The PRO version also works in multi-site installations.', 'duplicator'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Display admin notice if duplicator pro is enabled
     *
     * @return void
     */
    public static function tempWarningProEnableNotice()
    {
        ?>
        <div class="error notice">
            <p>
        <?php echo 'DUPLICATOR LITE: ' . __('With the next version Duplicator LITE cannot be activated if PRO is active.', 'duplicator'); ?>
            </p>
            <p>
                TODO: insert description here
            </p>
        </div>
        <?php
    }

    /**
     * Display admin notice if duplicator pro is enabled
     *
     * @return void
     */
    public static function tempWarningMultisiteNotice()
    {
        ?>
        <div class="error notice">
            <p>
        <?php echo 'DUPLICATOR LITE: ' .
        __('With the next version of duplicator, it will no longer be possible to enable it in multisite installations.', 'duplicator');
        ?>
            </p>
            <p>
                TODO: insert description here
            </p>
        </div>
        <?php
    }
}
