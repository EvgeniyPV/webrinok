<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<tr>
    <td class="col-opt">Create</td>
    <td>
        Run all CREATE SQL statements at once.  This option should be checked when source database tables have foreign key relationships.
        When choosing this option there might be a chance of a timeout error.  Uncheck this option to split CREATE queries in chunks.
        This option is checked by default.
    </td>
</tr>