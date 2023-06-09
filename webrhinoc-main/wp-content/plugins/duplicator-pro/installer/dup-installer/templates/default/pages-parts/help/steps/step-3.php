<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<!-- ============================================
STEP 3
============================================== -->
<?php
$sectionId   = 'section-step-3';
$expandClass = $sectionId == $open_section ? 'open' : 'close';
?>
<section id="<?php echo $sectionId; ?>" class="expandable <?php echo $expandClass; ?>" >
    <h2 class="header expand-header">Step <span class="step">3</span> of 4: Update Data</h2>
    <div class="content" >
        <a class="help-target" name="help-s3"></a>
        <div id="dup-help-step2" class="help-page">

            <!-- SETTINGS-->
            <h3>Setup</h3>
            Configure search and replace options, set an existing user's password, add a new user and change activation status of plugins.
            <br/><br/>

            <h3>Engine</h3>

            <h4>Custom Search & Replace</h4>
            Permits adding as many custom search and replace items that you would like. 
            Use extreme caution when using this feature as it can have unintended consequences as it will search the entire database.   
            It is recommended to only use highly unique items such as full URL or file paths with this option.
            
            <h4>Database Scan Options</h4>
            <table class="help-opt">
                <?php
                dupxTplRender('pages-parts/help/widgets/option-heading');
                dupxTplRender('pages-parts/help/options/scan-options/heading');
                dupxTplRender('pages-parts/help/options/scan-options/cleanup');
                dupxTplRender('pages-parts/help/options/scan-options/email-domains');
                dupxTplRender('pages-parts/help/options/scan-options/database-search');
                dupxTplRender('pages-parts/help/options/scan-options/post-guid');
                dupxTplRender('pages-parts/help/options/scan-options/max-size-check-for-serialize-objects');
                ?> 
            </table>

            <br/>
            <h3>Database Scan Options</h3>
            <h4>Existing Admin Password Reset</h4>
            You can set a new existing administrator user password.
            
            <h4>New Admin Account</h4>
            <table class="help-opt">
                <?php
                dupxTplRender('pages-parts/help/widgets/option-heading');
                dupxTplRender('pages-parts/help/options/new-admin-account/create-new-user');
                dupxTplRender('pages-parts/help/options/new-admin-account/username');
                dupxTplRender('pages-parts/help/options/new-admin-account/password');
                dupxTplRender('pages-parts/help/options/new-admin-account/email');
                dupxTplRender('pages-parts/help/options/new-admin-account/nickname');
                dupxTplRender('pages-parts/help/options/new-admin-account/first-name');
                dupxTplRender('pages-parts/help/options/new-admin-account/last-name');
                ?>
            </table>

            <br/><br/>
            <h3>Plugins</h3>
            Here, All Plugins are listed in the Plugin List table. All plugins are grouped Must-Use, Drop-In, Active and Inactive plugins.
            You should check the plugin which you would like to retain activated. You should uncheck the plugin which you would like to deactivate.

            <br/>
            <h4>WP-Config File</h4>
                In this section, You can configure different constants in the wp-config.php file.
            <table class="help-opt">
                <?php
                dupxTplRender('pages-parts/help/widgets/option-heading');
                dupxTplRender('pages-parts/help/options/wp-config-file/add-remove-switch');
                dupxTplRender('pages-parts/help/options/wp-config-file/constants');
                dupxTplRender('pages-parts/help/options/wp-config-file/auth-keys');
                ?>
            </table>
        </div>
    </div>
</section>