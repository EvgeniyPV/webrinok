<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<div id="important-final-step-warning" class="margin-bottom-1" >
    <b><i class="fa fa-exclamation-triangle"></i> IMPORTANT FINAL STEPS:</b> 
    Login into the WordPress Admin to remove all <?php DUPX_View_Funcs::helpLink('step4', 'installation files'); ?> and finalize the install process.<br> 
    This install is <u>NOT</u> complete until all installer files have been completely removed.<br>
    Leaving any of the installer files on this server can lead to security issues.
</div>