<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?><div class="dup-pro-import-upload-message" >
    <p class="import-upload-reset-message-error">
        <i class="fa fa-exclamation-triangle"></i> <b><?php DUP_PRO_U::esc_html_e('UPLOAD FILE PROBLEM'); ?></b>
    </p>
    <p>
        <?php DUP_PRO_U::_e('Error message:'); ?><b> <span class="import-upload-error-message"><!-- here is set the message received from the server --></span></b>
    </p>
    <div><?php DUP_PRO_U::_e('Possible solutions:'); ?></div>
    <ul class="dup-pro-simple-style-list" >
        <li>
            <?php printf(DUP_PRO_U::__('Change the chunk size in <a href="%s">settings</a> and try again'), 'admin.php?page=duplicator-pro-settings&tab=import'); ?>
        </li>
        <li>
            <?php printf(DUP_PRO_U::__('Upload the file via FTP/file manager to the "%s" folder and reload the page.'), esc_html(DUPLICATOR_PRO_PATH_IMPORTS)); ?>
        </li>
    </ul>
</div>