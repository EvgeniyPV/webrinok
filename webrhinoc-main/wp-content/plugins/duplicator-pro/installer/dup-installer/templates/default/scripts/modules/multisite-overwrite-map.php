<?php

/**
 *
 * @package Duplicator/Installer
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapJson;

$paramsManager = PrmMng::getInstance();
?>
<script>
    const subsiteOwrMapWrapper = <?php echo SnapJson::jsonEncode($paramsManager->getFormWrapperId(PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING)); ?>;
    const subsiteOwrMapInputName = <?php echo SnapJson::jsonEncode(PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING); ?>;

    (function($) {
        DUPX.owrMapper = {
            'wrapperNode'    : null,
            'itemListNode'   : null,
            'addItemNode'    : null,
            'addButton'      : null,
            'newItemTeplate' : null,
            'listInfo'       : null,
            'init' : function () {
                this.wrapperNode =  $('#' + subsiteOwrMapWrapper);
                if (this.wrapperNode.length == 0) {
                    return;
                }
                this.itemListNode = this.wrapperNode.find('.overwrite_sites_list');
                this.listInfo     = this.itemListNode.data('list-info');
                this.addItemNode  = this.itemListNode.find('.overwrite_site_item.add_item');
                this.initAddButton();
            },
            'initAddButton': function () {
                let thisObj = this;

                this.addButton      = this.addItemNode.find('.add_button');
                this.newItemTeplate = $(this.addButton.data('new-item'));

                this.addButton.click(function () {
                    thisObj.addItem();
                });

                this.getItemsList().each(function () {
                    thisObj.initItemEvents($(this));
                });
            },
            'addItem': function () {
                if (!this.canAddNewItem()) {
                    return;
                }
                
                let itemList = this.getItemsList();
                let newItem  = this.newItemTeplate.clone();

                newItem.find(':disabled').prop('disabled', false);
                newItem.insertBefore(this.addItemNode);
                DUPX.initJsSelect(newItem.find('.js-select'));

                this.setSourceIdOptionsEnabled(itemList, newItem, true);
                this.changeSelectSourceId();
                
                this.setTargetIdOptionsEnabled(itemList, newItem, true);
                this.changeSelectTargetId();
                
                this.initItemEvents(newItem);
                this.setAddItemButtonStatus();
                this.setRemoveItemButtonStatus();
                this.updateLimitMessages();
                DUPX.reavelidateOnChangeAction();
            },
            'initItemEvents': function (item) {
                let thisObj = this;
                let sourceId = item.find('.source_id');
                let targetId = item.find('.target_id');
                let newSlug  = item.find('.new_slug');
                let sourceNoteSlug = item.find('.source-site-note .site-slug');
                let targetNoteSlug = item.find('.target-site-note .site-slug');

                item.find('.del_item').click(function () {
                    thisObj.removeItem(item);
                });

                sourceId.change(function () {
                    let currentVal = parseInt($(this).val());
                    thisObj.changeSelectSourceId();
                    sourceNoteSlug.text(thisObj.listInfo.sourceInfo.sites['id_' + currentVal].slug);
                });
                
                targetId.change(function () {
                    let currentVal = parseInt($(this).val());
                    thisObj.changeSelectTargetId();
                    item.find('.new-slug-wrapper').toggleClass('no-display', (currentVal > 0));
                    if (currentVal == 0) {
                        newSlug.trigger('input');
                    } else {
                        targetNoteSlug.text(thisObj.listInfo.targetInfo.sites['id_' + currentVal].slug);
                    }
                });

                newSlug.on('input',function(e){
                    if (targetId.val() != 0) {
                        return;
                    }
                    let newVal = $(this).val();
                    if (newVal.length == 0) {
                        targetNoteSlug.text('_____');
                    } else {
                        targetNoteSlug.text($(this).val());
                    }
                });

                sourceId.trigger('change');
                targetId.trigger('change');
            },
            'changeSelectSourceId': function () {
                let thisObj = this;
                let itemList = this.getItemsList();

                itemList.each(function () {
                    thisObj.setSourceIdOptionsEnabled(itemList, $(this), false);
                });
            },
            'setSourceIdOptionsEnabled': function (itemList, currentItem, autoSelect) {
                let selectObj = currentItem.find('.source_id');
                let alreadySelectedIds = itemList.not(currentItem).find('.source_id').map(function(idx, elem) {
                    return parseInt($(elem).val());
                }).get();

                selectObj.find('option').each(function () {
                    let currentValue = parseInt($(this).attr('value'));
                    let isAlreadySelected = ($.inArray(currentValue, alreadySelectedIds) > -1);
                    $(this).prop('disabled', isAlreadySelected);
                });

                if (autoSelect) {
                    selectObj.find('option:not([disabled]):first').prop('selected', true);
                    selectObj.trigger('change');
                }
            },
            'changeSelectTargetId': function (selectObj) {
                let thisObj = this;
                let itemList = this.getItemsList();

                itemList.each(function () {
                    thisObj.setTargetIdOptionsEnabled(itemList, $(this), false);
                });
            },
            'setTargetIdOptionsEnabled': function (itemList, currentItem, autoSelect) {
                let selectObj = currentItem.find('.target_id');
                let alreadySelectedIds = itemList.not(currentItem).find('.target_id').map(function(idx, elem) {
                    return parseInt($(elem).val());
                }).get();

                selectObj.find('option').each(function () {
                    let currentValue = parseInt($(this).attr('value'));
                    if (currentValue == 0) {
                        return;
                    }
                    let isAlreadySelected = ($.inArray(currentValue, alreadySelectedIds) > -1);
                    $(this).prop('disabled', isAlreadySelected);
                });

                if (autoSelect) {
                    selectObj.find('option:not([disabled]):first').prop('selected', true);
                    selectObj.trigger('change');
                }
            },
            'updateFormData': function(formData) {
                if (this.wrapperNode.length == 0) {
                    return formData;
                }
                let itemsList = this.getItemsList();
                let paramValue = [];
                let nameSourceId = itemsList.first().find('.source_id').attr('name').replace(/(.+)\[\]/, '$1');
                let nameTargetId = itemsList.first().find('.target_id').attr('name').replace(/(.+)\[\]/, '$1');
                let nameNewSlug  = itemsList.first().find('.new_slug').attr('name').replace(/(.+)\[\]/, '$1');

                itemsList.each(function() {
                    let newObj = {
                        'sourceId': $(this).find('.source_id').val(),
                        'targetId': $(this).find('.target_id').val(),
                        'newSlug' : $(this).find('.new_slug').val()
                    };
                    paramValue.push(newObj);
                });
                delete formData[nameSourceId];
                delete formData[nameTargetId];
                delete formData[nameNewSlug];
                formData[subsiteOwrMapInputName] = JSON.stringify(paramValue);

                return formData;
            },
            'getItemsList': function () {
                return this.itemListNode.find('.overwrite_site_item:not(.title):not(.add_item)');
            },
            'canAddNewItem': function () {
                let numItems = this.getItemsList().length;
                return (
                    numItems < this.listInfo.sourceInfo.numSites &&
                    numItems < this.listInfo.hardLimit
                );
            },
            'canRemoveItem': function () {
                return (this.getItemsList().length > 1);
            },
            'setAddItemButtonStatus' : function () {
                this.addButton.prop('disabled', !this.canAddNewItem());
            },
            'setRemoveItemButtonStatus' : function () {
                let thisObj = this;

                this.getItemsList().each(function () {
                    $(this).find('.del_item').toggleClass('disabled', !thisObj.canRemoveItem());
                });
            },
            'updateLimitMessages' : function () {
                let numItems = this.getItemsList().length;
                if (numItems >= this.listInfo.hardLimit) {
                    this.addItemNode.find('.overwrite_site_soft_limit_msg').addClass('no-display');
                    this.addItemNode.find('.overwrite_site_hard_limit_msg').removeClass('no-display');
                } else if (numItems >= this.listInfo.softLimit) {
                    this.addItemNode.find('.overwrite_site_soft_limit_msg').removeClass('no-display');
                    this.addItemNode.find('.overwrite_site_hard_limit_msg').addClass('no-display');
                } else {
                    this.addItemNode.find('.overwrite_site_soft_limit_msg').addClass('no-display');
                    this.addItemNode.find('.overwrite_site_hard_limit_msg').addClass('no-display');
                }
            },
            'removeItem': function (itemNode) {
                if (!this.canRemoveItem()) {
                    return;
                }
                itemNode.remove();
                this.changeSelectSourceId();
                this.changeSelectTargetId();
                this.setAddItemButtonStatus();
                this.setRemoveItemButtonStatus();
                this.updateLimitMessages();
                DUPX.reavelidateOnChangeAction();
            }
        }

        $(document).ready(function() {
            DUPX.owrMapper.init();
        });
    })(jQuery);
</script>