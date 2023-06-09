<?php
/* @var $global DUP_PRO_Global_Entity */
defined("ABSPATH") or die("");

DUP_PRO_U::hasCapability('manage_options');

$nonce_action    = 'duppro-settings-import-edit';
$action_updated  = null;
$action_response = DUP_PRO_U::__("Import Settings Saved");

$global = DUP_PRO_Global_Entity::get_instance();

//SAVE RESULTS
if (isset($_POST['action']) && $_POST['action'] == 'save_import_settings') {
    DUP_PRO_U::verifyNonce($_POST['_wpnonce'], $nonce_action);
    $global->import_chunk_size = filter_input(INPUT_POST, 'import_chunk_size', FILTER_VALIDATE_INT, array('options' => array('default' => DUPLICATOR_PRO_DEFAULT_CHUNK_UPLOAD_SIZE)));

    $action_updated                     = $global->save();
}
?>

<style>    
    table.form-table tr td { padding-top: 25px; }
</style>

<form id="dup-settings-form" action="<?php echo self_admin_url('admin.php?page=' . DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG); ?>" method="post" data-parsley-validate>
    <?php wp_nonce_field($nonce_action); ?>
    <input type="hidden" name="action" value="save_import_settings">
    <input type="hidden" name="page"   value="<?php echo DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG ?>">
    <input type="hidden" name="tab"   value="import">

    <?php if ($action_updated) : ?>
        <div class="notice notice-success is-dismissible dpro-wpnotice-box"><p><?php echo $action_response; ?></p></div>
    <?php endif; ?> 

    <h3 id="duplicator-pro-import-settings" class="title"><?php DUP_PRO_U::esc_html_e("Import Settings"); ?></h3>
    <hr size="1" />
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="input_import_chunk_size" ><?php DUP_PRO_U::esc_html_e("Upload Chunk Size"); ?></label>
            </th>
            <td >
                <select name="import_chunk_size" id="input_import_chunk_size" class="postform">
                    <?php foreach (DUP_PRO_CTRL_import::getChunkSizes() as $size => $label) { ?>
                        <option value="<?php echo $size; ?>" <?php selected($global->import_chunk_size, $size); ?>><?php echo esc_html($label); ?></option>
                    <?php } ?>
                </select>

                <p class="description">
                    <?php DUP_PRO_U::esc_html_e("Connections size are from slowest to fastest.  If you have issue uploading a package start with a lower size."); ?>
                </p>
                
            </td>
        </tr>
    </table>

    <p class="submit dpro-save-submit">
        <input type="submit" name="submit" id="submit" class="button-primary" value="<?php DUP_PRO_U::esc_attr_e('Save Import Settings') ?>" style="display: inline-block;" />
    </p>
</form>