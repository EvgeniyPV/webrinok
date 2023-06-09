<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<tr>
    <td class="col-opt">Mode</td>
    <td>
        Modes affect the SQL syntax MySQL supports (and others such as MariaDB) .  This setting performs various data validation checks.  This makes it easier 
        to use MySQL in different environments and to use MySQL together with other database servers.  It is very useful when running into conversion issues.
        The following options are supported:
        <ul>
            <li><b>Default:</b>  This is the recommend setting to use.  It will use the current Database mode setting</li>
            <li><b>Disable:</b> This will prevent the database engine from running in any mode</li>
            <li><b>Custom:</b> This option will allow you to enter in a custom set of mode commands.  See the documentation link below for options.</li>
        </ul>
        
        For a full overview please see the  <a href="https://dev.mysql.com/doc/refman/8.0/en/sql-mode.html" target="_blank">MySQL mode</a> and
        <a href="https://mariadb.com/kb/en/sql-mode/" target="_blank">MariaDB mode</a>  specific to  your version.     To add a custom setting enable the
        Custom radio button and enter in the mode(s) that needs to be applied.
    </td>
</tr>
