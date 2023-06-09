<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapJson;

$paramsManager = PrmMng::getInstance();
?>
<script>
    const createNewInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_WP_ADMIN_CREATE_NEW)); ?>;

    $(document).ready(function () {
        $('#' + createNewInputId).change(function () {
            if ($(this).prop('checked')) {
                $('.new-admin-field, .new-admin-field > input').prop('disabled', false);
            } else {
                $('.new-admin-field, .new-admin-field > input').prop('disabled', true).val('').trigger('keyup').trigger('blur');
            }
        });
    });
</script>

