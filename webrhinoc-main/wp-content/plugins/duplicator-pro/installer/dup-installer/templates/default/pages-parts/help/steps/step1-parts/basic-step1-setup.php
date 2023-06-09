<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<h3>Setup</h3>
<h4>Database Connection</h4>
The database setup options allow you to connect to an existing database or create a new database in the case of cPanel connect or create a new database.
There are currently two options you can use to perform the database setup:
<ol>
    <li>Basic</li>
    <li>cPanel</li>
</ol>

<br/>
<b>Basic Setup</b>
<br/><br/>
The "Basic" option requires knowledge about the existing server and on most hosts will require that the database be setup ahead of time.
<table class="help-opt">
    <?php
    dupxTplRender('pages-parts/help/widgets/option-heading');
    dupxTplRender('pages-parts/help/options/database/action');
    dupxTplRender('pages-parts/help/options/database/host');
    dupxTplRender('pages-parts/help/options/database/database');
    dupxTplRender('pages-parts/help/options/database/user');
    dupxTplRender('pages-parts/help/options/database/password');
    ?>
</table>
<br/>

<br/>
<b>cPanel Database Login</b>
<br/><br/>
The cPanel option is for hosts that support cPanel Software. 
This option will automatically show you the existing databases and users on your 
cPanel server and allow you to create new databases directly from the installer.
<i>The cPanel connectivity option is only available for Duplicator Pro.</i>
<table class="help-opt">
    <?php
    dupxTplRender('pages-parts/help/widgets/option-heading');
    dupxTplRender('pages-parts/help/options/cpanel/host');
    dupxTplRender('pages-parts/help/options/cpanel/username');
    dupxTplRender('pages-parts/help/options/cpanel/password');
    dupxTplRender('pages-parts/help/options/cpanel/troubleshoot');
    ?>
</table>
<br/>

<h4>Site Details</h4>
The site options to chage new site url and archive action.
<table class="help-opt">
    <?php
    dupxTplRender('pages-parts/help/widgets/option-heading');
    dupxTplRender('pages-parts/help/options/site/new-site-url');
    dupxTplRender('pages-parts/help/options/site/archive-action');
    ?>
</table>
<br/>
<br/>