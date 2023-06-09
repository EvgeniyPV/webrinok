<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<tr>
    <td class="col-opt">Skip Files</td>
    <td>
        <p>
            <b>Extract all files:</b><br/>
            Extract all files from the package archive.  This option is selected by default.
        </p>
        <p>
            <b>Skip extraction of WP core files:</b><br/>
            Extract all files except WordPress core files. Choose this option to extract only the wp-content folder and other non-core files and directories.
        </p>
        <p>
            <b>Extract only media files and new plugins and themes:</b><br/>
            Extract all media files, new plugins, and new themes. The installer will not extract plugins and themes that already exist on the destination site.
        </p>
    </td>
</tr>