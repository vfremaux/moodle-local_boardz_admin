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

            var cursorX = parseInt(e.pageX);
            // var cursorY = parseInt(e.pageY);
            $('#entity-drop-form').css('left', cursorX - 330 + 'px');
            $('#entity-drop-form').css('top', 90 + 'px');

            $('#entity-drop-form').removeClass('local-boardz-admin-hide');
        },

        closeImportPopup: function(e) {
            $('#entity-drop-form').addClass('local-boardz-admin-hide');
            e.stopPropagation();
        },

        /* Unused at the moment : MVC implementation on view.php */
        importEntity: function() {

            var url = cfg.wwwroot + '/local/boardz_admin/ajax/services.php';
            url += '?sesskey=' + cfg.sesskey;
            url += '&what=import';
            url += '&importdata=' + $('textarea[name="entityimportdata"]').val();

            $.get(url, function() {
            }, 'json');

            $('#entity-drop-form').addClass('local-boardz-admin-hide');
        }

    };

    return boardzclipboard;
});