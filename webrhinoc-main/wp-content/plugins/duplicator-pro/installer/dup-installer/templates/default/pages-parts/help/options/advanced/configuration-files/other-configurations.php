<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<tr>
    <td class="col-opt">Other Configurations </td>
    <td>
        When dealing with configuration files (web.config and .user.ini), the installer can apply different modes:
        <br/><br/>

        <b>Create New:</b><br/>
        This is the default recommended option which will create either a new web.config or .user.ini file. 
        The new file is streamlined to help guarantee no conflicts are created during install. 
        The config files generated with this mode will be simple and basic. The Wordfence .user.ini file if
        present will be removed.
        <br/><br/>

        <b>Retain original from Archive.zip/daf:</b><br/>
        This option simply copies the /dup-installer/original_files_[HASH]/source_site_webconfig file to web.config. 
        The dup-installer/original_files_[HASH]/source_site_* files come from the original web server where the package was built.  
        Please note this option will cause issues with the install process if the configuration files are not properly setup to
        handle the new server environment.  
        This is an advanced option and should only be used if you know how to properly configure your web servers configuration.
        <br/><br/>

        <b>Ignore All:</b><br/>
        This option simply does nothing.  No files are backed up, nothing is renamed or created. 
        This advanced option assumes you already have your config files setup and know how they should behave in the new environment.  
        When the package is build it will always create a 
        /dup-installer/original_files_[HASH]/source_site_webconfig or dup-installer/original_files_[HASH]/source_site_userini.
        Since these files are already in the archive file they will show up when the archive is extracted.
        <br/><br/>

        <b>Additional Notes:</b>
        The origional web.config file and the origional .user.ini file are copied in the /dup-installer/original_files_[HASH] folder of the arhive.

        When using either 'Create New' or 'Retain original from Archive.zip/daf' any existing config files will be backed up to the folder 
        /wp-content/backups-dup-pro/installer/original_files_[HASH].
        <i>None of these changes are made until Step 3 is completed, to avoid any issues the configuration file might cause during the install.</i>
        <br/><br/>
    </td>
</tr>
