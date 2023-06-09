<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<script>
    DUPX.confirmDialog = {
        content: null,
        advCheckWrapper: null,
        advCheckCheckbox: null,
        init: function () {
            this.content = $("#db-install-dialog-confirm");
            this.advCheckWrapper = $("#db-install-dialog-confirm .advanced-confirm");
            this.advCheckCheckbox = $("#db-install-dialog-confirm #dialog-adv-confirm-check");
        },
        open: function () {
            if (this.content.length == 0) {
                return;
            }

            let thisObj = this;

            this.content.dialog({
                resizable: false,
                height: "auto",
                width: 700,
                modal: true,
                position: {my: 'top', at: 'top+150'},
                buttons: {
                    "OkButton": {
                        text: "OK",
                        id: "db-install-dialog-confirm-button",
                        click: function () {
                            if (!thisObj.advCheckWrapper.hasClass('no-display') && !thisObj.advCheckCheckbox.is(":checked")) {
                                return;
                            }
                            $(this).dialog("close");
                            DUPX.deployStep1();
                        }
                    },
                    "CancelButton": {
                        text: "Cancel",
                        id: "db-install-dialog-cancel-button",
                        click: function () {
                            $(this).dialog("close");
                        }
                    }
                }
            });
        },
        enableAdvCheck: function () {
            this.advCheckWrapper.removeClass('no-display');
        }, 
        disableAdvCheck: function () {
            this.advCheckWrapper.addClass('no-display');
        }
    };
</script>