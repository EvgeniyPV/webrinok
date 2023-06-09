<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<tr>
    <td class="col-opt">Action</td>
    <td>
        <b>Create New Database:</b><br/> Will attempt to create a new database if it does not exist. 
        When using the 'Basic' option this option will not work on many
        hosting providers as the ability to create new databases is normally locked down. 
        If the database does not exist then you will need to login to your
        control panel and create the database.  
        If your host supports 'cPanel' then you can use this option to create a new database after logging in via your
        cPanel account.
        <br/>
        <br/>

        <b>Overwrite Existing Tables:</b><br/>
        Overwrite only the tables that are extracted. 
        This option is useful if you want to install WordPress in a database containing other WordPress installations or applications.<br>
        <span style="font-style:italic">Note:</span> 
        When performing an install alongside another installation be sure to change 
        the prefix since only those tables with the same prefix be overwritten while tables of a different prefix will be retained.
        <br/>
        <br/>
        
        <b>Remove All Data:</b><br/>
        DELETE all tables in the database you are connecting to.  Please make sure you have
        backups of all your data before using an portion of the installer, as this option WILL remove all data.
        <br/>
        <br/>

        <b>Backup and Rename Existing Tables:</b><br/>
        RENAME all tables in the database you are connecting to with a prefix of
        "<?php echo $GLOBALS['DB_RENAME_PREFIX'] ?>".
        <br/>
        <br/>

        <b>Manual SQL Execution:</b>
        This option requires that you manually run your own SQL import to an existing database before running the installer.
        When this action is selected the dup-database__[hash].sql file found inside the dup-installer folder of the archive.zip file will NOT be processed. 
        The database your connecting to should already be a valid WordPress installed database.  
        This option is viable when you need to run advanced search and replace options on the database.
        <br/>
        <br/>

    </td>
</tr>
