<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<tr>
    <td class="col-opt">WordPress wp-config</td>
    <td>
        <b>Create New:</b><br/>
        This is the default recommended option which will modify the origional wp-config file.
        <br/><br/>

        <b>Do nothing:</b><br/>
        This option simply does nothing. The wp-config file doesn't get backed up, renamed, or created. This advanced option assumes you already 
        know how it should behave in the new environment.  This option is for advanced technical persons.
        <br/><br/>

        <b>Create new from wp-config sample:</b><br/>
        This option create a new wp-config file by modifying the wp-config-sample.php file.
        The new wp-config.php file will behave like it is created in wordpress fresh default installation.
        <br/><br/>
    </td>
</tr>
