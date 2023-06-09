<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapIO;

require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/ui/class.ui.dialog.php');

$packageDeteleConfirm                      = new DUP_PRO_UI_Dialog();
$packageDeteleConfirm->title               = DUP_PRO_U::__('Delete Package?');
$packageDeteleConfirm->wrapperClassButtons = 'dpro-dlg-import-detete-package-btns';
$packageDeteleConfirm->progressOn          = false;
$packageDeteleConfirm->closeOnConfirm      = true;
$packageDeteleConfirm->message             = DUP_PRO_U::__('Are you sure you want to delete the selected package?');
$packageDeteleConfirm->jsCallback          = 'DupPro.ImportManager.removePackage()';
$packageDeteleConfirm->initConfirm();

$packageInvalidName                  = new DUP_PRO_UI_Messages(DUP_PRO_U::__('<b>Invalid archive name:</b> The archive name must follow the Duplicator package name pattern'
    . ' e.g. PACKAGE_NAME_[HASH]_[YYYYMMDDHHSS]_archive.zip (or with a .daf extension). <br>Please make sure not to rename the archive after downloading it and try again!'), DUP_PRO_UI_Messages::ERROR);
$packageInvalidName->hide_on_init    = true;
$packageInvalidName->is_dismissible  = true;
$packageInvalidName->auto_hide_delay = 10000;
$packageInvalidName->initMessage();

$packageAlreadyExists                  = new DUP_PRO_UI_Messages(DUP_PRO_U::__('Archive file name already exists! <br>Please remove it and try again!'), DUP_PRO_UI_Messages::ERROR);
$packageAlreadyExists->hide_on_init    = true;
$packageAlreadyExists->is_dismissible  = true;
$packageAlreadyExists->auto_hide_delay = 5000;
$packageAlreadyExists->initMessage();

$packageUploaded                  = new DUP_PRO_UI_Messages(DUP_PRO_U::__('Package uploaded'), DUP_PRO_UI_Messages::NOTICE);
$packageUploaded->hide_on_init    = true;
$packageUploaded->is_dismissible  = true;
$packageUploaded->auto_hide_delay = 5000;
$packageUploaded->initMessage();

$packageCancelUpload                  = new DUP_PRO_UI_Messages(DUP_PRO_U::__('Package upload cancelled'), DUP_PRO_UI_Messages::ERROR);
$packageCancelUpload->hide_on_init    = true;
$packageCancelUpload->is_dismissible  = true;
$packageCancelUpload->auto_hide_delay = 5000;
$packageCancelUpload->initMessage();

$packageRemoved                  = new DUP_PRO_UI_Messages(DUP_PRO_U::__('Package removed'), DUP_PRO_UI_Messages::NOTICE);
$packageRemoved->hide_on_init    = true;
$packageRemoved->is_dismissible  = true;
$packageRemoved->auto_hide_delay = 5000;
$packageRemoved->initMessage();

$importChunkSize = DUP_PRO_CTRL_import::getChunkSize();
?><script>
    jQuery(document).ready(function ($) {
        var uploadFileMessageContent = <?php echo json_encode(SnapIO::getInclude(dirname(__FILE__) . '/import-message-upload-error.php')); ?>;

        DupPro.ImportManager = {
            uploader: $('#dup-pro-import-upload-file'),
            uploaderContent: $('#dup-pro-import-upload-file-content'),
            packageRowTemplate: $('#dup-pro-import-row-template'),
            packageRowNoFoundTemplate: $('#dup-pro-import-available-packages-templates .dup-pro-import-no-package-found'),
            packagesAviable: $('#dpro-pro-import-available-packages'),
            packagesList: $('#dpro-pro-import-available-packages .packages-list'),
            packageRowUploading: null,
            packageRowToDelete: null,
            autoLaunchAfterUpload: false,
            autoLaunchLink: false,
            confirmLaunchLink: false,
            startUpload: false,
            lastUploadsTimes: [],
            debug: true,
            init: function () {
                $('#dup-pro-import-instructions-toggle').click(function () {
                    $('#dup-pro-import-instructions-content').toggle(300);
                })

                DupPro.ImportManager.uploader.upload({
                    autoUpload: true,
                    multiple: false,
                    maxSize: <?php echo empty($importChunkSize) ? wp_max_upload_size() : 10737418240; ?>, //100GB get value from upload_max_filesize
                    maxConcurrent: 1,
                    maxFiles: 1,
                    postData: {
                        action: 'duplicator_pro_import_upload',
                        nonce: <?php echo json_encode(wp_create_nonce('duplicator_pro_import_upload')); ?>
                    },
                    chunkSize: <?php echo $importChunkSize; ?>, // This is in kb
                    action: <?php echo json_encode(get_admin_url(null, 'admin-ajax.php')); ?>,
                    chunked: <?php echo empty($importChunkSize) ? 'false' : 'true'; ?>,
                    label: DupPro.ImportManager.uploaderContent.parent().html(),
                    leave: '<?php echo esc_js(DUP_PRO_U::__('You have uploads pending, are you sure you want to leave this page?')); ?>'
                })
                        .on("start.upload", DupPro.ImportManager.onStart)
                        .on("complete.upload", DupPro.ImportManager.onComplete)
                        .on("filestart.upload", DupPro.ImportManager.onFileStart)
                        .on("fileprogress.upload", DupPro.ImportManager.onFileProgress)
                        .on("filecomplete.upload", DupPro.ImportManager.onFileComplete)
                        .on("fileerror.upload", DupPro.ImportManager.onFileError)
                        .on("fileerror.chunkerror", DupPro.ImportManager.onChunkError);

                DupPro.ImportManager.uploaderContent.remove();
                DupPro.ImportManager.uploaderContent = $('#dup-pro-import-upload-file #dup-pro-import-upload-file-content');
                DupPro.ImportManager.initPageButtons();
                DupPro.ImportManager.checkMaxUploadedFiles();

                DupPro.ImportManager.packagesList.on('click', '.dup-pro-import-action-remove', function () {
                    event.stopPropagation();
                    DupPro.ImportManager.packageRowToDelete = $(this).closest('.dup-pro-import-package');
<?php $packageDeteleConfirm->showConfirm(); ?>
                    return false;
                });

                DupPro.ImportManager.packagesList.on('click', '.dup-pro-import-action-package-detail-toggle', function () {
                    event.stopPropagation();
                    let button = $(this);
                    let details = button.closest('.dup-pro-import-package').find('.dup-pro-import-package-detail');
                    if (details.hasClass('no-display')) {
                        button.find('.fa').removeClass('fa-caret-down').addClass('fa-caret-up');
                        details.removeClass('no-display');
                    } else {
                        button.find('.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
                        details.addClass('no-display');
                    }
                    return false;
                });

                DupPro.ImportManager.packagesList.on('click', '.dup-pro-import-action-cancel-upload', function () {
                    event.stopPropagation();
                    DupPro.ImportManager.abortUpload();
<?php $packageCancelUpload->showMessage(); ?>
                    return false;
                });

                DupPro.ImportManager.packagesList.on('click', '.dup-pro-import-action-install', function () {
                    event.stopPropagation();
                    DupPro.ImportManager.confirmLaunchLink = $(this).data('install-url');
                    $('#dup-pro-import-phase-one').addClass('no-display');
                    $('#dup-pro-import-phase-two').removeClass('no-display');
                    return false;
                });

            },
            initPageButtons: function () {
                $('.dup-pro-import-view-list').click(function () {
                    event.stopPropagation();
                    DupPro.ImportManager.updateViewMode('<?php echo DUP_PRO_CTRL_import::VIEW_MODE_ADVANCED; ?>');
                });

                $('.dup-pro-import-view-single').click(function () {
                    event.stopPropagation();
                    DupPro.ImportManager.updateViewMode('<?php echo DUP_PRO_CTRL_import::VIEW_MODE_BASIC; ?>');
                });

                $('.dup-pro-open-help-link').click(function () {
                    $('#contextual-help-link').show();
                });

                $('#dup-pro-import-launch-installer-confirm').click(DupPro.ImportManager.confirmLaunchInstaller);
                $('#dup-pro-import-launch-installer-cancel').click(function () {
                    event.stopPropagation();
                    DupPro.ImportManager.confirmLaunchLink = false;
                    $('#dup-pro-import-phase-two').addClass('no-display');
                    $('#dup-pro-import-phase-one').removeClass('no-display');
                    return false;
                });
            },
            confirmLaunchInstaller: function () {
                event.stopPropagation();
                window.location.href = DupPro.ImportManager.confirmLaunchLink;
                return false;
            },
            onStart: function (e, files)
            {
                DupPro.ImportManager.startUpload = true;
                DupPro.ImportManager.uploader.upload("disable");
                DupPro.ImportManager.autoLaunchLink = false;

                let isValidName = true;
                let alreadyExists = false;

                $.each(files, function (index, value) {
                    if (!DupPro.ImportManager.isValidFileName(value.name)) {
                        isValidName = false;
                    }

                    if (DupPro.ImportManager.isAlreadyExists(value.name)) {
                        alreadyExists = true;
                    }
                });

                if (!isValidName) {
<?php $packageInvalidName->showMessage(); ?>
                    DupPro.ImportManager.abortUpload();
                    return;
                }

                if (alreadyExists) {
<?php $packageAlreadyExists->showMessage(); ?>
                    DupPro.ImportManager.abortUpload();
                    return;
                }
            },
            onComplete: function (e)
            {
                if (DupPro.ImportManager.autoLaunchAfterUpload && DupPro.ImportManager.autoLaunchLink) {
                    document.location.href = DupPro.ImportManager.autoLaunchLink;
                }
                DupPro.ImportManager.checkMaxUploadedFiles();
            },
            onFileStart: function (e, file)
            {
                DupPro.ImportManager.packagesList.find('.dup-pro-import-no-package-found').remove();
                DupPro.ImportManager.packageRowUploading = DupPro.ImportManager.packageRowTemplate.clone().prependTo(DupPro.ImportManager.packagesList);

                DupPro.ImportManager.packageRowUploading.removeAttr('id');
                DupPro.ImportManager.packageRowUploading.find('.name .text').text(file.name);
                DupPro.ImportManager.packageRowUploading.find('.size').text(Duplicator.Util.humanFileSize(file.size));
                DupPro.ImportManager.packageRowUploading.find('.created').html("<i><?php DUP_PRO_U::_e('loading...'); ?></i>");

                let loader = DupPro.ImportManager.packageRowUploading.find('.funcs .dup-pro-loader').removeClass('no-display');
                loader.find('.dup-pro-meter > span').css('width', '0%');
                loader.find('.text').text('0%');
            },
            onFileProgress: function (e, file, percent, eventObj)
            {
                let position = 0;
                let total = file.size;
                if ('currentChunk' in file) {
                    position = file.currentChunk * file.chunkSize;
                } else {
                    if (eventObj.lengthComputable) {
                        position = eventObj.loaded || eventObj.position;
                    } else {
                        position = false;
                    }
                }

                if (position !== false) {
                    DupPro.ImportManager.addUploadTime(position);
                    percent = Math.round((position / total) * 100 * 10) / 10;
                    percent = Number.isInteger(percent) ? percent + ".0" : percent; 
                }

                let timeLeft = DupPro.ImportManager.getTimeLeft(total - position);
                let loader = DupPro.ImportManager.packageRowUploading.find('.funcs .dup-pro-loader');
                loader.find('.dup-pro-meter > span').css("width", percent + "%");
                loader.find('.text').text(percent + "% - " + DupPro.ImportManager.millisecToTime(timeLeft));
            },
            onFileComplete: function (e, file, response)
            {

                var result = JSON.parse(response);
                console.log('RESPONSE ', result);

                if (result.success == false) {
                    DupPro.ImportManager.removeRow(DupPro.ImportManager.packageRowUploading);
                    DupPro.ImportManager.packageRowUploading = null;
                    DupPro.addAdminMessage(uploadFileMessageContent, 'error', {
                        'hideDelay': 5000,
                        'updateCallback': function (msgNode) {
                            msgNode.find('.import-upload-error-message').text(result.data.message);
                        }
                    });
                    return;
                }

                DupPro.ImportManager.packageRowUploading.data('path', result.data.funcData.fullPath);
                if (result.data.funcData.isImportable) {
                    DupPro.ImportManager.packageRowUploading.addClass('is-importable');
                    DupPro.ImportManager.packageRowUploading
                            .find('.dup-pro-import-action-install')
                            .prop('disabled', false)
                            .data('install-url', result.data.funcData.installerPageLink);
                    DupPro.ImportManager.autoLaunchLink = result.data.funcData.installerPageLink;
                } else {
                    DupPro.ImportManager.autoLaunchLink = false;
                }
                DupPro.ImportManager.packageRowUploading.find('.dup-pro-import-package-detail').html(result.data.funcData.htmlDetails);
                DupPro.ImportManager.packageRowUploading.find('.created').text(result.data.funcData.created);
                DupPro.ImportManager.packageRowUploading.find('.funcs .dup-pro-loader').addClass('no-display');
                DupPro.ImportManager.packageRowUploading.find('.funcs .actions').removeClass('no-display');
                DupPro.ImportManager.packageRowUploading = null;
<?php $packageUploaded->showMessage(); ?>
            },
            onFileError: function (e, file, error)
            {
                DupPro.ImportManager.removeRow(DupPro.ImportManager.packageRowUploading);
                DupPro.ImportManager.packageRowUploading = null;
                if (error === 'abort') {
                    // no message for abort
                    return;
                }

                DupPro.addAdminMessage(uploadFileMessageContent, 'error', {
                    'hideDelay': 5000,
                    'updateCallback': function (msgNode) {
                        if (error == 'size') {
                            error = '<?php DUP_PRO_U::esc_html_e('The file size exceeds the maximum upload limit.'); ?>';
                        }
                        msgNode.find('.import-upload-error-message').text(error);
                    }
                });
            },
            getTimeLeft: function (sizeToFinish) {
                if (DupPro.ImportManager.lastUploadsTimes.length < 2) {
                    return false;
                }
                let pos1 = DupPro.ImportManager.lastUploadsTimes[0].pos;
                let time1 = DupPro.ImportManager.lastUploadsTimes[0].time;

                let index = DupPro.ImportManager.lastUploadsTimes.length - 1
                let pos2 = DupPro.ImportManager.lastUploadsTimes[index].pos;
                let time2 = DupPro.ImportManager.lastUploadsTimes[index].time;

                let deltaPos = pos2 - pos1;
                let deltaTime = time2 - time1;

                return deltaTime / deltaPos * sizeToFinish;
            },
            millisecToTime: function (s) {
                if (s <= 0) {
                    return 'loading...';
                }

                var ms = s % 1000;
                s = (s - ms) / 1000;
                var secs = s % 60;
                s = (s - secs) / 60;
                var mins = s % 60;
                var hrs = (s - mins) / 60;

                let result = '';
                if (hrs > 0) {
                    result += ' ' + hrs + ' hr';
                }

                if (mins > 0) {
                    result += ' ' + (mins + 1) + ' min';
                    return result;
                }

                return secs + ' sec';
            },
            addUploadTime: function (postion) {
                if (DupPro.ImportManager.lastUploadsTimes.length > 20) {
                    DupPro.ImportManager.lastUploadsTimes.shift();
                }

                DupPro.ImportManager.lastUploadsTimes.push({
                    'pos': postion,
                    'time': Date.now()
                });
            },
            updateContentMessage: function (icon, line1, line2) {
                DupPro.ImportManager.uploaderContent.find('.message').html('<i class="fas ' + icon + ' fa-sm"></i> ' + line1 + '<br>' + line2);
            },
            isAlreadyExists: function (name) {

                let alreadyExists = false;
                DupPro.ImportManager.packagesList.find('tbody .name .text').each(function () {
                    if (name === $(this).text()) {
                        alreadyExists = true;
                    }
                });

                return alreadyExists;
            },
            isValidFileName: function (name) {
                if (!name.match(<?php echo DUPLICATOR_PRO_ARCHIVE_REGEX_PATTERN; ?>)) {
                    return false;
                }
                return true;
            },
            abortUpload: function () {
                try {
                    DupPro.ImportManager.uploader.upload("abort");
                } catch (err) {
                    // prevent abort error
                }
                DupPro.ImportManager.removeRow(DupPro.ImportManager.packageRowUploading);
                DupPro.ImportManager.packageRowUploading = null;
            },
            removePackage: function () {
                Duplicator.Util.ajaxWrapper({
                    action: 'duplicator_pro_import_package_delete',
                    path: DupPro.ImportManager.packageRowToDelete.data('path'),
                    nonce: '<?php echo wp_create_nonce('duplicator_pro_import_package_delete'); ?>'
                },
                        function (funcData, data, textStatus, jqXHR) {
                            DupPro.ImportManager.removeRow(DupPro.ImportManager.packageRowToDelete);
<?php $packageRemoved->showMessage(); ?>;
                            return '';
                        }
                );
            },
            removeRow: function (row) {
                if (row) {
                    row.fadeOut('fast',
                            function () {
                                row.remove();
                                if (DupPro.ImportManager.packagesList.find('.dup-pro-import-package').length === 0) {
                                    DupPro.ImportManager.packageRowNoFoundTemplate.clone().appendTo(DupPro.ImportManager.packagesList);
                                }
                                DupPro.ImportManager.checkMaxUploadedFiles();
                            }
                    );
                }
            },
            checkMaxUploadedFiles: function () {
                let limit = 0; // 0 no limit       
                let numPackages = $('.packages-list .dup-pro-import-package').length;

                if ($('#dpro-pro-import-available-packages').hasClass('view-single-item')) {
                    limit = 1;
                }

                if (limit > 0 && numPackages >= limit) {
                    DupPro.ImportManager.uploader.upload("disable");
                } else {
                    DupPro.ImportManager.uploader.upload("enable");
                }
            },
            updateViewMode: function (viewMode) {
                Duplicator.Util.ajaxWrapper({
                    action: 'duplicator_pro_import_set_view_mode',
                    nonce: '<?php echo wp_create_nonce('duplicator_pro_import_set_view_mode'); ?>',
                    view_mode: viewMode
                },
                        function (funcData, data, textStatus, jqXHR) {
                            switch (funcData) {
                                case '<?php echo DUP_PRO_CTRL_import::VIEW_MODE_ADVANCED; ?>':
                                    $('.dup-pro-import-view-single').removeClass('active');
                                    $('.dup-pro-import-view-list').addClass('active');
                                    $('#dup-pro-basic-mode-message').addClass('no-display');
                                    DupPro.ImportManager.packagesAviable.removeClass('view-single-item').addClass('view-list-item');
                                    break;
                                case '<?php echo DUP_PRO_CTRL_import::VIEW_MODE_BASIC; ?>':
                                    $('.dup-pro-import-view-list').removeClass('active');
                                    $('.dup-pro-import-view-single').addClass('active');
                                    $('#dup-pro-basic-mode-message').removeClass('no-display');
                                    DupPro.ImportManager.packagesAviable.removeClass('view-list-item').addClass('view-single-item');
                                    break;
                                default:
                                    throw '<?php DUP_PRO_U::_e('Invalid view mode'); ?>';
                            }
                            DupPro.ImportManager.checkMaxUploadedFiles();
                            return '';

                        },
                        function (funcData, data, textStatus, jqXHR) {
                            DupPro.addAdminMessage(data.message, 'error', {'hideDelay': 5000});
                            return '';
                        }
                );
            },
            console: function () {
                if (this.debug) {
                    if (arguments.length > 1) {
                        console.log(arguments[0], arguments[1]);
                    } else {
                        console.log(arguments[0]);
                    }
                }
            }
        };

        // wait form stone init, it's not a great method but for now I haven't found a better one.
        window.setTimeout(DupPro.ImportManager.init, 500);

        $('.dup-pro-import-box.closable').each(function () {
            let box = $(this);
            let title = $(this).find('.box-title');
            let content = $(this).find('.box-content');

            title.click(function () {
                if (box.hasClass('opened')) {
                    box.removeClass('opened').addClass('closed');
                } else {
                    box.removeClass('closed').addClass('opened');
                }
            });
        });
    });
</script>