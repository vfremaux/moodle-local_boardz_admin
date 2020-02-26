/*
 *
 */
// jshint unused:false, undef:false

define(['jquery', 'core/config', 'core/log'], function($, cfg, log) {

    var boardzclipboard = {

        init: function() {
            $('.snappable').bind('click', this.snapToClipboard);
            $('#entity-drop-button').bind('click', this.openImportPopup);
            $('#entity-drop-button-close').bind('click', this.closeImportPopup);
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

            log.debug(e.pageX + ' ' + e.pageY);
            $('#entity-drop-form').css('top', e.pageX - 60 + 'px');
            $('#entity-drop-form').css('left', e.pageY - 310 + 'px');

            $('#entity-drop-form').removeClass('local-boardz-admin-hide');
            e.stopPropagation();
        },

        closeImportPopup: function(e) {
            $('#entity-drop-form').addClass('local-boardz-admin-hide');
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

            $('#entity-drop-form').addClass('local-boardz-admin-hide');
        }

    };

    return boardzclipboard;
});