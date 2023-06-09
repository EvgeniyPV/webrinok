<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<tr>
    <td class="col-opt">Apache .htaccess </td>
    <td>
        When dealing with the .htaccess file, the installer can apply different modes:
        <br/><br/>

        <b>Create New:</b><br/>
        This is the default recommended option which will create a new .htaccess file.  The new .htaccess file is streamlined to help
        guarantee no conflicts are created during install.
        <br/><br/>

        <b>Retain original from Archive.zip/daf:</b><br/>
        This option simply copies the /dup-installer/original_files_[HASH]/.htaccess file to .htaccess file. 
        Please note this option will cause issues with the install process if the .htaccess are not properly setup to
        handle the new server environment.  
        This is an advanced option and should only be used if you know how to properly configure your .htaccess configuration.
        <br/><br/>

        <b>Do nothing:</b><br/>
        This option simply does nothing.  The .htaccess doesn't back up, renamed, or created.  
        This advanced option assumes you already have your
        .htaccess file setup and know how it should behave in the new environment.  
        When the package is build it will always create a /dup-installer/original_files_[HASH]/.htaccess.
        Since the file is already in the archive file they will show up when the archive is extracted.
        <br/><br/>

        <b>Additional Notes:</b>
        Inside the archive.zip or archive.daf will be a copy of the original .htaccess (Apache) file that was setup with your packaged site. 
        The .htaccess file is copied to /dup-installer/original_files_[HASH]/source_site_htaccess. 
        When using either 'Create New' or 'Retain original from Archive.zip/daf' an existing .htaccess file will be backed 
        up to a /wp-content/backups-dup-pro/installer/original_files_[HASH]/source_site_htaccess.
        <i>This change will not made until the final step is completed, to avoid any issues the .htaccess might cause during the install</i>
        <br/><br/>
    </td>
</tr>
