/*! dup import installer */
(function ($) {
    DupProImportInstaller = {
        installerIframe: $('#dpro-pro-import-installer-iframe'),
        preventUnlodaPage: false,
        init: function () {
            /*
             DupProImportInstaller.installerIframe.on("load", function () {
             DupProImportInstaller.resizeIframe();
             });

             setInterval(DupProImportInstaller.resizeIframe, 250); */

            window.onbeforeunload = function () {
                if (DupProImportInstaller.preventUnlodaPage) {
                    return "If you leave this page you will lose your unsaved changes.";
                } else {
                    return;
                }
            };

            DupProImportInstaller.installerIframe.on("load", function () {

                DupProImportInstaller.installerIframe.contents()
                    .find('#page-step1')
                    .on('click', '> .ui-dialog #db-install-dialog-confirm-button', function () {
                        DupProImportInstaller.preventUnlodaPage = true;
                        $('#dup-pro-import-installer-modal').removeClass('no-display');
                    });
            });
        },
        resizeIframe: function () {
            let height = DupProImportInstaller.installerIframe.contents()
                .find('html').css('overflow', 'hidden')
                .outerHeight(true);
            console.log('height', height);
            DupProImportInstaller.installerIframe.css({
                'height': height + 'px'
            })
        }
    }

    DupProImportInstaller.init();

})(jQuery);