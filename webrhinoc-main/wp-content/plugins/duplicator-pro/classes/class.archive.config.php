<?php

defined("ABSPATH") or die("");
/**
 * @copyright 2016 Snap Creek LLC
 */
class DUP_PRO_Archive_Config
{
    //READ-ONLY: COMPARE VALUES
    public $created;
    public $version_dup;
    public $version_wp;
    public $version_db;
    public $version_php;
    public $version_os;
    public $dbInfo;
//READ-ONLY: GENERAL
    public $opts_delete;
    public $blogname;
    public $wproot;
    public $wplogin_url;
    public $relative_content_dir;
    public $relative_plugins_dir;
    public $relative_theme_dirs;
    public $exportOnlyDB;
//PRE-FILLED: GENERAL
    public $secure_on;
    public $secure_pass;
    public $skipscan;
    public $dbhost;
    public $dbname;
    public $dbuser;
    public $dbpass;
    public $cache_wp;
    public $cache_path;
//PRE-FILLED: CPANEL
    public $cpnl_host;
    public $cpnl_user;
    public $cpnl_pass;
    public $cpnl_enable;
//MULTI-SITE
    public $wp_tableprefix;
    public $mu_mode;
    public $mu_generation;
    public $subsites;
    public $main_site_id;
    public $mu_is_filtered;
//MISC
    public $license_limit;
//BRAND
    public $brand;
    public $wp_content_dir_base_name;
    function __construct()
    {
        $this->subsites = array();
    }
}
