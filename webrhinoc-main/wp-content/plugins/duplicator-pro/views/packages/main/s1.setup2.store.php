<?php defined("ABSPATH") or die(""); ?>
<style>
    /*STORAGE: Area*/
    div.storage-filters {display:inline-block; padding: 0 10px 0 10px}
    tr.storage-missing td, tr.storage-missing td a {color: #A62426 !important }
</style>

<!-- ===================
META-BOX: STORAGE -->
<div class="dup-box" id="dup-pack-storage-panel-area">
    <div class="dup-box-title" id="dpro-store-title">
        <i class="fas fa-database fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Storage') ?> <sup id="dpro-storage-title-count" class="dup-box-title-badge"></sup>
        <button class="dup-box-arrow">
            <span class="screen-reader-text"><?php DUP_PRO_U::esc_html_e('Toggle panel:') ?> <?php DUP_PRO_U::esc_html_e('Storage Options') ?></span>
        </button>
    </div>          

    <div class="dup-box-panel" id="dup-pack-storage-panel" style="<?php echo esc_attr($ui_css_storage); ?>">
        <div style="padding:0 0 4px 0">
            <?php DUP_PRO_U::esc_html_e('Choose the storage location(s) where the archive and installer files will be saved.') ?>
        </div>
        <table class="widefat package-tbl">
            <thead>
                <tr>
                    <th style='white-space: nowrap; width:10px;'></th>
                    <th style='width:275px'><?php DUP_PRO_U::esc_html_e('Name') ?></th>
                    <th style='width:100px'><?php DUP_PRO_U::esc_html_e('Type') ?></th>
                    <th style="white-space: nowrap"><?php DUP_PRO_U::esc_html_e('Location') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 0;
            foreach ($storage_list as $store) {
                try {
                    if (!$store->is_authorized()) {
                        continue;
                    }

                    //Sometime storage is authorized
                    //      then server downgrade to lower php version
                    // For ex. When storage is added PHP CURL extension enabled
                    //      But now It is disabled, It cause to fatal error
                    //          in the Package creation step 1
                    if (!DUP_PRO_StorageSupported::isStorageObjStorageTypeSupported($store)) {
                        continue;
                    }

                    $store_type = $store->get_storage_type_string();
                    $store_id   = $store->get_storage_type();
                    $i++;
                    $store_location = $store->get_storage_location_string();
                    $is_valid = $store->is_valid();
                    $is_checked = in_array($store->id, $global->manual_mode_storage_ids) && $is_valid;
                    $mincheck = ($i == 1) ? 'data-parsley-mincheck="1" data-parsley-required="true"' : '';
                    $row_style = ($i % 2) ? 'alternate' : '';
                    $row_style .= ($is_valid) ? '' : ' storage-missing';
                    ?>
                        <tr class="package-row <?php echo esc_attr($row_style); ?>">
                            <td>
                                <input class="duppro-storage-input" <?php echo DUP_PRO_UI::echoDisabled($is_valid == false); ?> name="_storage_ids[]" onclick="DupPro.Pack.UpdateStorageCount(); return true;" data-parsley-errors-container="#storage_error_container" <?php echo $mincheck; ?> type="checkbox" value="<?php echo intval($store->id); ?>" <?php DUP_PRO_UI::echoChecked($is_checked); ?> />
                                <input name="edit_id" type="hidden" value="<?php echo intval($i); ?>" />
                            </td>
                            <td>
                                <a href="?page=duplicator-pro-storage&tab=storage&inner_page=edit&storage_id=<?php echo intval($store->id); ?>" target="_blank">
                                <?php
                                    echo ($is_valid == false)  ? '<i class="fa fa-exclamation-triangle fa-sm"></i> '  : '';
                                    echo esc_html($store->name);
                                ?>
                                </a>
                            </td>
                            <td>
                                <?php
                                    echo DUP_PRO_Storage_Entity::getStorageIcon($store_id) . '&nbsp;';
                                    echo esc_html($store_type);
                                ?>
                            </td>
                            <td>
                                <?php
                                    //TODO:  Move logic to a new method like DUP_PRO_Storage_Entity::getLink();
                                    $safe_store_loc = wp_kses($store_location, array('a' => array('href' => array(),'title' => array())));
                                    switch ($store_id) {
                                        case DUP_PRO_Storage_Types::Local :
                                        case DUP_PRO_Storage_Types::OneDrive :
                                        case DUP_PRO_Storage_Types::OneDriveMSGraph :
                                            echo  str_replace('<a', '<a target="_blank" ', $safe_store_loc);
                                            break;
                                        case DUP_PRO_Storage_Types::GDrive :
                                            echo "<a href='https://drive.google.com/drive/' target='_blank'>" . urldecode($store_location) . "</a>";
                                            break;
                                        default:
                                            echo "<a href='{$store_location}' target='_blank'>" . urldecode($store_location) . "</a>";
                                            break;
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php
                } catch (Exception $e) {
                    echo "<tr><td colspan='5'><i>"
                    . DUP_PRO_U::__('Unable to load storage type.  Please validate the setup.')
                    . "</i></td></tr>";
                }
            }
            ?>
            </tbody>
        </table>
        <div style="text-align: right; margin:4px 4px -4px 0; padding:0; width: 100%">
            <a href="admin.php?page=duplicator-pro-storage&tab=storage&inner_page=edit" target="_blank">
                [<?php DUP_PRO_U::esc_html_e('Add Storage') ?>]
            </a>
        </div>
    </div>
</div>

<div id="storage_error_container" class="duplicator-error-container"></div>

<script>
    jQuery(function ($)
    {
        DupPro.Pack.UpdateStorageCount = function ()
        {
            var store_count = $('#dup-pack-storage-panel input[name="_storage_ids[]"]:checked').length;
            $('#dpro-storage-title-count').html('(' + store_count + ')');
            (store_count == 0)
                    ? $('#dpro-storage-title-count').css({'color': 'red', 'font-weight': 'bold'})
                    : $('#dpro-storage-title-count').css({'color': '#444', 'font-weight': 'normal'});
        }
    });

//INIT
    jQuery(document).ready(function ($)
    {
        DupPro.Pack.UpdateStorageCount();
    });
</script>
