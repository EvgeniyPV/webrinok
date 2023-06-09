<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<h3>Options (For Advanced mode)</h3>
The advanced options allow you to change database table prefix, advanced options and other configuration options in the wp-config.php file.

<br/>

<h4>Database</h4>
<table class="help-opt">
    <?php
    dupxTplRender('pages-parts/help/widgets/option-heading');

    dupxTplRender('pages-parts/help/options/advanced/general/heading');
    dupxTplRender('pages-parts/help/options/advanced/general/site-title');
    dupxTplRender('pages-parts/help/options/advanced/general/keep-users');

    dupxTplRender('pages-parts/help/options/advanced/database/database-settings-heading');
    dupxTplRender('pages-parts/help/options/advanced/database/table-prefix');
    ?>
</table>

<h4>Advanced</h4>
These are the advanced options for advanced users.

<table class="help-opt">
    <?php
    // Engine Settings
    dupxTplRender('pages-parts/help/widgets/option-heading');
    dupxTplRender('pages-parts/help/options/advanced/engine-settings/heading');
    dupxTplRender('pages-parts/help/options/advanced/engine-settings/extraction-mode');
    dupxTplRender('pages-parts/help/options/advanced/engine-settings/skip-files');
    dupxTplRender('pages-parts/help/options/advanced/engine-settings/database-mode');

    // Processing
    dupxTplRender('pages-parts/help/options/advanced/processing/heading');
    dupxTplRender('pages-parts/help/options/advanced/processing/safe-mode');
    dupxTplRender('pages-parts/help/options/advanced/processing/file-times');
    dupxTplRender('pages-parts/help/options/advanced/processing/logging');
    dupxTplRender('pages-parts/help/options/advanced/processing/file-permissions');
    dupxTplRender('pages-parts/help/options/advanced/processing/dir-permissions');
    dupxTplRender('pages-parts/help/options/advanced/processing/remove-inactive-plugins-and-themes');

    // Configuration files
    dupxTplRender('pages-parts/help/options/advanced/configuration-files/heading');
    dupxTplRender('pages-parts/help/options/advanced/configuration-files/wordpress-wp-config');
    dupxTplRender('pages-parts/help/options/advanced/configuration-files/apache-htaccess');
    dupxTplRender('pages-parts/help/options/advanced/configuration-files/other-configurations');
    ?>
</table>

<h4>URLs & Paths</h4>
In the tab "URLs & Paths", You can see and configure below paths and URLs:
    <ul>
        <li>
            WP core path
        </li>
        <li>
            WP core URL
        </li>
        <li>
            WP-content path
        </li>
        <li>
            WP-content URL
        </li>
        <li>
            Uploads path
        </li>
        <li>
            Uploads URL
        </li>
        <li>
            Plugins path
        </li>
        <li>
            Plugins URL
        </li>
        <li>
            MU-plugins path
        </li>
        <li>
            MU-plugins URL
        </li>
    </ul>

    These paths and URLs are set autoamatically by the Package installer. 
    You can set these paths and URLs manually. 
    If you are changing it, Please make sure you are putting right path or URL.
<br/><br/>