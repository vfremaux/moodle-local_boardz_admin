/*
 *
 */
// jshint unused:false, undef:false

define(['jquery', 'core/config', 'core/log'], function($, cfg, log) {

    var boardzclipboard = {

        init: function() {
            $('.snappable').bind('click', this.snapToClipboard);
            $('#entity-drop-button').bind('click', this.openImportPopup);
            $('#entity-import-button').bind('click', this.importEntity);
            log.debug("AMD Boardz clipboard initialized");
        },

        /*
         * Snappable links or icons must define a "data-target" to designate
         * the text source.
         */
        snapToClipboard: function() {

            var that = $(this);

            var strtarget = that.attr('data-target');
            var str;
            if (strtarget === 'self') {
                str = that.attr("data-str");
            } else {
                str = $('#' + strtarget).attr("data-str");
            }

            var el = document.createElement('textarea');
            el.value = str;
            el.setAttribute('readonly', '');
            el.style.position = 'absolute';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
        },

        openImportPopup: function(e) {

            log.debug(e.offsetX + ' ' + e.offsetY);
            $('#entity-drop-from').css('top', e.offsetX - 60);
            $('#entity-drop-from').css('top', e.offsetY + 10);

            $('#entity-drop-from').removeClass('local-boardz-admin-hide');
            e.stopPropagation();
        },

        importEntity: function() {

            var url = cfg.wwwroot + '/local/boardz_admin/ajax/services.php';
            url += '?sesskey=' + cfg.sesskey;
            url += '&what=import';
            url += '&importdata=' + ('textarea[name="entityimportdata"]').val();

            $.get(url, function() {
                document.location.reload(true);
            }, 'json');

            $('#entity-drop-from').addClass('local-boardz-admin-hide');
        }

    };

    return boardzclipboard;
});