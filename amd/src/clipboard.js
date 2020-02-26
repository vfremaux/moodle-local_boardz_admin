/*
 *
 */
// jshint unused:false, undef:false

define(['jquery', 'core/log'], function($, log) {

    var boardzclipboard = {

        init: function() {
            $('.snappable').bind('click', this.snapToClipboard);
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
    };

    return boardzclipboard;
});