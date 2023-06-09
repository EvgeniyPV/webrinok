<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<tr>
    <td class="col-opt">Safe Mode</td>
    <td>
        Safe mode is designed to configure the site with specific options at 
        install time to help over come issues that may happen during the install were the site
        is having issues. These options should only be used if you run into issues after you have tried to run an install.
        <br/><br/>

        <b>Basic:</b><br/>
        This safe mode option will disable all the plugins at install time. 
        When this option is set you will need to re-enable all plugins after the
        install has full ran.
        <br/><br/>

        <b>Advanced:</b><br/>
        This option applies all settings used in basic and will also de-activate and 
        reactivate your theme when logging in for the first time.  This
        options should be used only if the Basic option did not work.
    </td>
</tr>