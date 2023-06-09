<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Addons\ProBase\License\License;
use Duplicator\Core\Views\TplMng;

$tplMng = TplMng::getInstance();

$multisite_css = is_multisite() ? '' : 'display:none';
$subsite_filter_css = (is_multisite() && License::isBusiness()) ? '' : 'display:none';

DUP_PRO_LOG::trace("subsite filter css: {$subsite_filter_css}");
$archive_format = $global->archive_build_mode == DUP_PRO_Archive_Build_Mode::DupArchive ? 'daf' : 'zip';
?>

<style>
    /*ARCHIVE: Area*/
    form#dup-form-opts div.tabs-panel{max-height:800px; padding:20px 15px 15px 15px; min-height:300px}
    form#dup-form-opts ul li.tabs{font-weight:bold}
    select#archive-format {min-width:100px; margin:1px 0px 4px 0px}
    span#dup-archive-filter-file {color:#A62426; display:none; vertical-align: top;}
    span#dup-archive-filter-db {color:#A62426; display:none; vertical-align: top;}
    span#dup-archive-db-only {color:#A62426; display:none; vertical-align: top;}
    /* Tab: Files */
    form#dup-form-opts textarea#filter-dirs {height:165px; padding:7px}
    form#dup-form-opts textarea#filter-exts {height:27px}
    form#dup-form-opts textarea#filter-files {height:165px; padding:7px}
    div.dup-tabs-opts-help {font-style:italic; font-size:11px; margin:5px 0; color:#666}
    
     /* Tab: Multisite */
    table.mu-mode td {padding: 10px}
    table.mu-opts td {padding: 10px}
    select.mu-selector {
        height:175px !important; 
        width:450px; 
        max-width: 450px
    }
    select.mu-selector option {
        padding: 2px 0;
    }
    button.mu-push-btn {padding: 5px; width:40px; font-size:14px}
</style>

<!-- ===================
 META-BOX: ARCHIVE -->
<div class="dup-box">
<div class="dup-box-title" >
    <i class="far fa-file-archive fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Archive') ?> 
    <sup class="dup-box-title-badge">
        <a href="admin.php?page=duplicator-pro-settings&tab=package" target="_blank"><?php echo esc_html($archive_format); ?></a>
    </sup> &nbsp;
    <span style="font-size:13px">
        <span id="dup-archive-filter-file" title="<?php DUP_PRO_U::esc_attr_e('Folder/File Filter Enabled') ?>">
            <i class="fas fa-folder-open"></i><sup><i class="fas fa-filter fa-xs"></i></sup> &nbsp;&nbsp;&nbsp;
        </span>
        <span id="dup-archive-filter-db" title="<?php DUP_PRO_U::esc_attr_e('Database Filter Enabled') ?>">
            <i class="fas fa-table"></i><sup><i class="fas fa-filter fa-xs"></i></sup>
        </span>
        <span id="dup-archive-db-only" title="<?php DUP_PRO_U::esc_attr_e('Archive Only the Database') ?>">
            <?php DUP_PRO_U::esc_html_e('Database Only') ?>
        </span>
    </span>
    <button class="dup-box-arrow">
        <span class="screen-reader-text"><?php DUP_PRO_U::esc_html_e('Toggle panel:') ?> <?php DUP_PRO_U::esc_html_e('Archive Settings') ?></span>
    </button>
</div>      
<div class="dup-box-panel" id="dup-pack-archive-panel" style="<?php echo esc_attr($ui_css_archive); ?>">
    <input type="hidden" name="archive-format" value="ZIP" />

    <!-- ===================
    NESTED TABS -->
    <div data-dpro-tabs="true">
        <ul>
            <li class="filter-files-tab"><?php DUP_PRO_U::esc_html_e('Files') ?></li>
            <li class="filter-db-tab"><?php DUP_PRO_U::esc_html_e('Database') ?></li>
            <li class="filter-mu-tab" style="<?php echo $multisite_css ?>"><?php DUP_PRO_U::esc_html_e('Multisite') ?></li>
        </ul>

        <!-- ===================
        TAB1: FILES -->
        <div>
            <?php
            $uploads = wp_upload_dir();
            $upload_dir = SnapIO::safePath($uploads['basedir']);
            $content_path = defined('WP_CONTENT_DIR') ? SnapIO::safePath(WP_CONTENT_DIR) : '';
            ?>

            <div class="dup-package-hdr-1">
                <?php DUP_PRO_U::esc_html_e("Filters") ?>
            </div>

            <div class="dup-form-item">
                <span class="title"><?php DUP_PRO_U::esc_html_e("Database Only") ?>:</span>
                <span class="input">
                    <input type="checkbox" id="export-onlydb" name="export-onlydb" onclick="DupPro.Pack.ExportOnlyDB()"/>
                     <label for="export-onlydb"><?php DUP_PRO_U::esc_html_e('Archive Only the Database') ?></label>
                </span>
            </div>

            <div class="dup-form-item" id="dup-file-filter-label">
                <span class="title"><?php DUP_PRO_U::esc_html_e("Enable Filters") ?>:</span>
                <span class="input">
                    <input type="checkbox" id="filter-on" name="filter-on" onclick="DupPro.Pack.ToggleFileFilters()" />
                    <label for="filter-on"><?php DUP_PRO_U::esc_html_e("Allow Folder, File Extension &amp; Files to be Excluded") ?></label>
                    <i class="fas fa-question-circle fa-sm"
                       data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("File Filters"); ?>"
                       data-tooltip="<?php DUP_PRO_U::esc_attr_e('File filters allow you to ignore directories/files and file extensions.  When creating a package only include the data you '
                        . 'want and need.  This helps to improve the overall archive build time and keep your backups simple and clean.'); ?>">
                    </i>
                </span>
            </div>

            <div id="dup-exportdb-items-off">
                <div id="dup-file-filter-items">

                    <!-- DIRECTORIES -->
                    <div class="dup-form-item">
                        <label for="filter-dirs" title="<?php DUP_PRO_U::esc_attr_e("Separate all filters by semicolon"); ?>">
                            <b><?php DUP_PRO_U::esc_html_e("Folders") ?>:</b>
                            <sup class="dup-badge-01" title="<?php DUP_PRO_U::esc_attr_e("Number of diectory filters") ?>" id="filter-dirs-count">(0)</sup>
                        </label>
                        <div class='dup-quick-links'>
                            <a href="javascript:void(0)" onclick="DupPro.Pack.AddExcludePath('<?php echo esc_js(duplicator_pro_get_home_path()); ?>')">[<?php DUP_PRO_U::esc_html_e("root path") ?>]</a>
                            <?php if (! empty($content_path)) :?>
                                <a href="javascript:void(0)" onclick="DupPro.Pack.AddExcludePath('<?php echo SnapIO::safePath(WP_CONTENT_DIR); ?>')">[<?php DUP_PRO_U::esc_html_e("wp-content") ?>]</a>
                            <?php endif; ?>
                            <a href="javascript:void(0)" onclick="DupPro.Pack.AddExcludePath('<?php echo rtrim($upload_dir, '/'); ?>')">[<?php DUP_PRO_U::esc_html_e("wp-uploads") ?>]</a>
                            <a href="javascript:void(0)" onclick="DupPro.Pack.AddExcludePath('<?php echo SnapIO::safePath(WP_CONTENT_DIR); ?>/cache')">[<?php DUP_PRO_U::esc_html_e("cache") ?>]</a>
                            <a href="javascript:void(0)" onclick="jQuery('#filter-dirs').val(''); DupPro.Pack.CountFilters();"><?php DUP_PRO_U::esc_html_e("(clear)") ?></a>
                        </div>
                        <textarea name="filter-dirs" id="filter-dirs" placeholder="/full_path/exclude_path1;/full_path/exclude_path2;"></textarea>
                    </div><br/>

                    <!-- EXTENSIONS -->
                    <div class="dup-form-item">
                        <label class="no-select" title="<?php DUP_PRO_U::esc_attr_e("Separate all filters by semicolon"); ?>">
                            <b><?php DUP_PRO_U::esc_html_e("File Extensions") ?>:</b>
                        </label>
                        <div class='dup-quick-links'>
                            <a href="javascript:void(0)" onclick="DupPro.Pack.AddExcludeExts('avi;mov;mp4;mpeg;mpg;swf;wmv;aac;m3u;mp3;mpa;wav;wma')">[<?php DUP_PRO_U::esc_html_e("media") ?>]</a>
                            <a href="javascript:void(0)" onclick="DupPro.Pack.AddExcludeExts('zip;rar;tar;gz;bz2;7z')">[<?php DUP_PRO_U::esc_html_e("archive") ?>]</a>
                            <a href="javascript:void(0)" onclick="jQuery('#filter-exts').val('')"><?php DUP_PRO_U::esc_html_e("(clear)") ?></a>
                        </div>
                        <textarea name="filter-exts" id="filter-exts" placeholder="ext1;ext2;ext3;"></textarea>
                    </div><br/>

                    <!-- FILES -->
                    <div class="dup-form-item">
                        <label class="no-select" title="<?php DUP_PRO_U::esc_attr_e("Separate all filters by semicolon"); ?>">
                            <b><?php DUP_PRO_U::esc_html_e("Files") ?>:</b>
                            <sup class="dup-badge-01" title="<?php DUP_PRO_U::esc_attr_e("Number of file filters") ?>" id="filter-files-count">(0)</sup>
                        </label>
                        <div class='dup-quick-links'>
                            <a href="javascript:void(0)" onclick="DupPro.Pack.AddExcludeFilePath('<?php echo esc_js(duplicator_pro_get_home_path()); ?>')"><?php DUP_PRO_U::esc_html_e("(file path)") ?></a>
                            <a href="javascript:void(0)" onclick="jQuery('#filter-files').val(''); DupPro.Pack.CountFilters();"><?php DUP_PRO_U::esc_html_e("(clear)") ?></a>
                        </div>
                        <textarea name="filter-files" id="filter-files" placeholder="/full_path/exclude_file_1.ext;/full_path/exclude_file2.ext"></textarea>
                    </div>

                    <div class="dup-tabs-opts-help">
                        <?php DUP_PRO_U::esc_html_e("The directories, extensions and files above will be be exclude from the archive file if enable is checked."); ?> <br/>
                        <?php
                        DUP_PRO_U::esc_html_e("Use full path for directories or specific files.");
                        echo " <b>";
                        DUP_PRO_U::esc_html_e("Use filenames without paths to filter same-named files across multiple directories.");
                        echo "</b>";
                        ?> <br/>
                        <?php DUP_PRO_U::esc_html_e("Use semicolons to separate all items. Use # to comment a line."); ?>
                    </div>
                </div>
            </div>

            <!-- DB ONLY ENABLED -->
            <div id="dup-exportdb-items-checked">
                <?php
                    echo wp_kses(
                        DUP_PRO_U::__("<b>Overview:</b><br> This advanced option excludes all files from the archive.  Only the database and a copy of the installer.php "
                        . "will be included in the archive.zip file. The option can be used for backing up and moving only the database."),
                        array(
                            'b' => array(),
                            'br' => array(),
                        )
                    );
                    echo '<br/><br/>';

                    echo wp_kses(
                        DUP_PRO_U::__("<b><i class='fa fa-exclamation-circle'></i> Notice:</b><br/>  Installing only the database over an existing site may have unintended consequences.  "
                        . "Be sure to know the state of your system before installing the database without the associated files.  "),
                        array(
                            'b' => array(),
                            'i' => array('class'),
                            'br' => array()
                        )
                    );

                    DUP_PRO_U::esc_html_e("For example, if you have WordPress 5.6 on this site and you copy this sites database to a host that has WordPress 5.8 files "
                        . "then the source code of the files  will not be in sync with the database causing possible errors.  This can also be true of plugins and themes.  "
                        . "When moving only the database be sure to know the database will be compatible with ALL source code files. Please use this advanced feature with "
                        . "caution!");

                    echo '<br/><br/>';

                    echo wp_kses(
                        DUP_PRO_U::__("<b>Install Time:</b><br> When installing a database only package please visit the "),
                        array(
                            'b' => array(),
                            'br' => array(),
                        )
                    );
                    ?>
                    <a href="https://snapcreek.com/duplicator/docs/quick-start/#quick-050-q" target="_blank"><?php DUP_PRO_U::esc_html_e('database only quick start'); ?></a>.

                <br/><br/>
            </div>


        </div>

        <!-- ===================
        TAB2: DATABASE -->
        <div>
            <?php $tplMng->render('parts/filters/tables_list_filter'); ?>
            <br/><br/>

            <div class="dup-package-hdr-1">
                <?php DUP_PRO_U::esc_html_e("Configuration") ?>
            </div>

            <div class="dup-form-item">
                <span class="title"><?php DUP_PRO_U::esc_html_e("SQL Mode") ?>:</span>
                <span class="input"><a href="?page=duplicator-pro-settings&tab=package" target="settings"><?php echo $dbbuild_mode; ?></a></span>
            </div>

             <div class="dup-form-item">
                <span class="title">
                    <?php DUP_PRO_U::esc_html_e("Compatibility Mode") ?>:
                    <i class="fas fa-question-circle fa-sm"
                       data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Compatibility Mode"); ?>"
                       data-tooltip="<?php DUP_PRO_U::esc_attr_e('This is an advanced database backwards compatibility feature that should ONLY be used if having problems installing packages.'
                               . ' If the database server version is lower than the version where the package was built then these options may help generate a script that is more compliant'
                               . ' with the older database server. It is recommended to try each option separately starting with mysql40.'); ?>">
                    </i>                    
                </span>
            </div>

            <?php
            if ($dbbuild_mode == 'MYSQLDUMP') :?>
                <?php
                    $modes = isset($Package) ? explode(',', $Package->Database->Compatible) : array();
                    $is_mysql40     = in_array('mysql40', $modes);
                    $is_no_table    = in_array('no_table_options', $modes);
                    $is_no_key      = in_array('no_key_options', $modes);
                    $is_no_field    = in_array('no_field_options', $modes);
                ?>
                <div class="dup-form-horiz-opts">
                    <span>
                        <input type="checkbox" name="dbcompat[]" id="dbcompat-mysql40" value="mysql40" <?php echo $is_mysql40 ? 'checked="true"' : ''; ?> >
                        <label for="dbcompat-mysql40"><?php DUP_PRO_U::esc_html_e("mysql40") ?></label> 
                    </span>
                    <span>
                        <input type="checkbox" name="dbcompat[]" id="dbcompat-no_table_options" value="no_table_options" <?php echo $is_no_table ? 'checked="true"' : ''; ?>>
                        <label for="dbcompat-no_table_options"><?php DUP_PRO_U::esc_html_e("no_table_options") ?></label>
                    </span>
                    <span>
                        <input type="checkbox" name="dbcompat[]" id="dbcompat-no_key_options" value="no_key_options" <?php echo $is_no_key ? 'checked="true"' : ''; ?>>
                        <label for="dbcompat-no_key_options"><?php DUP_PRO_U::esc_html_e("no_key_options") ?></label>
                    </span>
                    <span>
                        <input type="checkbox" name="dbcompat[]" id="dbcompat-no_field_options" value="no_field_options" <?php echo $is_no_field ? 'checked="true"' : ''; ?>>
                        <label for="dbcompat-no_field_options"><?php DUP_PRO_U::esc_html_e("no_field_options") ?></label>
                    </span>
                </div>
                <div class="dup-tabs-opts-help">
                    <?php DUP_PRO_U::esc_html_e("Compatibility mode settings are not persistent.  They must be enabled with every new build."); ?>&nbsp;
                    <a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-110-q" target="_blank">[<?php DUP_PRO_U::esc_html_e('full overview'); ?>]</a>
                </div>
            <?php else :?>
                &nbsp; &nbsp; <i><?php DUP_PRO_U::esc_html_e("This option is only available with mysqldump mode."); ?></i>
            <?php endif; ?>
            <br/>

        </div>

        <!-- ===================
        TAB3: MULTI-SITE -->
        <div >
            <div style="<?php echo $multisite_css ?>; max-width:900px">
            <?php
                $license = License::getType();

                echo '<b>' . DUP_PRO_U::esc_html__("Overview:") . '</b><br/>';
                $txt_mu_license = DUP_PRO_U::__("This Duplicator Pro <a href='admin.php?page=duplicator-pro-settings&tab=licensing' target='lic'>%s</a> has "
                    . "Multisite Basic capability, ");
                $txt_mu_basic   = DUP_PRO_U::__("which backs up and migrates an entire multisite network. "
                    . "Subsite to standalone conversion is not supported with Multisite Basic, only with Multisite Plus+.<br/><br/>"
                    . "To gain access to Multisite Plus+ please login to your dashboard and upgrade to either a <a href='https://snapcreek.com/dashboard/' target='snap'>Business or Gold License</a>.");

                switch ($license) {
                    case License::TYPE_PERSONAL:
                        printf(wp_kses($txt_mu_license, array('a' => array())), DUP_PRO_U::esc_html__("Personal License"));
                        echo $txt_mu_basic;
                        break;

                    case License::TYPE_FREELANCER:
                        printf(wp_kses($txt_mu_license, array('a' => array())), DUP_PRO_U::esc_html__("Freelancer License"));
                        echo $txt_mu_basic;
                        break;

                    case License::TYPE_BUSINESS_GOLD:
                        DUP_PRO_U::esc_html_e("When you want to move a full multisite network or convert a subsite to a standalone site just create a standard package like you would with a single site. "
                            . "Then browse to the installer and choose either 'Restore entire multisite network'  or 'Convert subsite into a standalone site'.  "
                            . "These options will be present on Step 1 of the installer when restoring a Multisite package.");

                        echo '<br/><br/>';
                        echo wp_kses(DUP_PRO_U::__("<u><b>Important:</b></u> Full network restoration is an installer option only if you include <b>all</b> subsites. If any subsites are filtered then you may only restore individual subsites as standalones sites at install-time."), array(
                                'b' => array(),
                                'u' => array(),
                            ));
                        break;

                    default:
                        printf($txt_mu_license, DUP_PRO_U::__("Unlicensed"));
                        echo $txt_mu_basic;
                }
                ?>
            </div>

            <?php if (is_multisite() && License::isBusiness()) :?>
                <table class="mu-opts">
                    <tr>
                        <td>
                            <b><?php DUP_PRO_U::esc_html_e("Included Sub-Sites"); ?>:</b><br/>
                            <select name="mu-include[]" id="mu-include" multiple="true" class="mu-selector">
                                <?php
                                foreach (DUP_PRO_MU::getSubsites() as $site) {
                                    echo "<option value='" . esc_attr($site->id) . "'>" . esc_html($site->domain . $site->path) . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <button type="button" id="mu-exclude-btn" class="mu-push-btn"><i class="fa fa-chevron-right"></i></button><br/>
                            <button type="button" id="mu-include-btn" class="mu-push-btn"><i class="fa fa-chevron-left"></i></button>
                        </td>
                        <td>
                            <b><?php DUP_PRO_U::esc_html_e("Excluded Sub-Sites"); ?>:</b><br/>
                            <select name="mu-exclude[]" id="mu-exclude" multiple="true" class="mu-selector"></select>
                        </td>
                    </tr>
                </table>

                <div class="dpro-panel-optional-txt" style="text-align: left">
                    <?php DUP_PRO_U::esc_html_e("This section allows you to control which sub-sites of a multisite network you want to include within your package.  The 'Included Sub-Sites' will also be available to choose from at install time."); ?> <br/>
                    <?php DUP_PRO_U::esc_html_e("By default all packages are include.  The ability to exclude sub-sites are intended to help shrink your package if needed."); ?>
                </div>
            <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<div class="duplicator-error-container"></div>
<?php
    $alert1 = new DUP_PRO_UI_Dialog();
    $alert1->title      = DUP_PRO_U::__('ERROR!');
    $alert1->message    = DUP_PRO_U::__('You can\'t exclude all sites.');
    $alert1->initAlert();
?>
<script>
jQuery(function($) 
{   
    /* METHOD: Toggle Archive file filter red icon */
    DupPro.Pack.ToggleFileFilters = function () 
    {
        var $filterItems = $('#dup-file-filter-items');
        if ($("#filter-on").is(':checked')) {
            $filterItems.prop('disabled', false).css({color: '#000'});
            $('#filter-exts, #filter-dirs, #filter-files').prop('readonly', false).css({color: '#000'});
            $('#dup-archive-filter-file').show();
        } else {
            $filterItems.attr('disabled', 'disabled').css({color: '#999'});
            $('#filter-dirs, #filter-exts, #filter-files').prop('readonly', true).css({color: '#999'});
            $('#dup-archive-filter-file').hide();
        }
    };

    DupPro.Pack.ExportOnlyDB = function ()
    {
        $('#dup-exportdb-items-off, #dup-exportdb-items-checked').hide();
        if ($("#export-onlydb").is(':checked')) {
            $('#dup-exportdb-items-checked').show();
            $('#dup-archive-db-only').show(100);
            $('#dup-archive-filter-db').hide();
            $('#dup-archive-filter-file, #dup-file-filter-label').hide();
        } else {
            $('#dup-exportdb-items-off, #dup-file-filter-label').show();
            $('#dup-exportdb-items-checked').hide();
            $('#dup-archive-db-only').hide();
            DupPro.Pack.ToggleFileFilters();
        }

        DupPro.Pack.ToggleDBFilters();
    };


    /* METHOD: Formats file directory path name on seperate line of textarea */
    DupPro.Pack.AddExcludePath = function (path) 
    {
        var text = $("#filter-dirs").val() + path + ';\n';
        $("#filter-dirs").val(text);
        DupPro.Pack.CountFilters();
    };

    /*  Appends a path to the extention filter  */
    DupPro.Pack.AddExcludeExts = function (path) 
    {
        var text = $("#filter-exts").val() + path + ';';
        $("#filter-exts").val(text);
    };

    DupPro.Pack.AddExcludeFilePath = function (path) 
    {
        var text = $("#filter-files").val() + path + '/file.ext;\n';
        $("#filter-files").val(text);
        DupPro.Pack.CountFilters();
    };

    DupPro.Pack.CountFilters = function()
    {
         var dirCount = $("#filter-dirs").val().split(";").length - 1;
         var fileCount = $("#filter-files").val().split(";").length - 1;
         $("#filter-dirs-count").html(' (' + dirCount + ')');
         $("#filter-files-count").html(' (' + fileCount + ')');
    }
 });
 
//INIT
jQuery(document).ready(function($) 
{
    //MU-Transfer buttons
    $('#mu-include-btn').click(function() {
        return !$('#mu-exclude option:selected').remove().appendTo('#mu-include');  
    });

    $('#mu-exclude-btn').click(function() {
        var include_all_count = $('#mu-include option').length;
        var include_selected_count = $('#mu-include option:selected').length;

        if(include_all_count > include_selected_count) {
            return !$('#mu-include option:selected').remove().appendTo('#mu-exclude');
        } else {
            <?php $alert1->showAlert(); ?>
        }
    });

    $("#filter-dirs").keyup(function()  {DupPro.Pack.CountFilters();});
    $("#filter-files").keyup(function() {DupPro.Pack.CountFilters();});

});
</script>
