/*
 *
 */
// jshint unused:false, undef:false

define(['jquery', 'core/log'], function($, log) {

    var boardzadminupdate = {
        init: function() {
            $('#mform1 select[name="classname"]').bind('change', this.enable_params);
            $('#mform1 select[name="classname"]').trigger('change');
            $('#mform1 select[name="classname"]').prop('disabled', null); // Enables back the class selector.

            log.debug("AMD boardz_admin update initialized");
        },

        /*
         * disables than enable settings matching the required boardz classname.
         */
        enable_params: function() {
            var that = $(this);

            // Hide all inputs.
            $('#mform1 input').prop('disabled', 'disabled');
            $('#mform1 input').each(function() {
                var requiredclass = that.val().replace(/\\/g, '-'); // TODO : Add for replace "all"
                // log.debug("RequiredClass " + requiredclass + " on " + $(this).attr('name'));
                if ($(this).hasClass(requiredclass)) {
                    // Reactivate all inputs of the required class.
                    $(this).prop('disabled', null);
                }
            });
            $('#mform1 select').prop('disabled', 'disabled');
            $('#mform1 select').each(function() {
                var requiredclass = that.val().replace(/\\/g, '-'); // TODO : Add for replace "all"
                // log.debug("RequiredClass " + requiredclass + " on " + $(this).attr('name'));
                if ($(this).hasClass(requiredclass)) {
                    // Reactivate all inputs of the required class.
                    $(this).prop('disabled', null);
                }
            });
            $('#mform1 select[name="classname"]').prop('disabled', null); // Enables back the class selector.

            $('#mform1 textarea').prop('disabled', 'disabled');
            $('#mform1 textarea').each(function() {
                var requiredclass = that.val().replace(/\\/g, '-'); // TODO : Add for replace "all"
                // log.debug("RequiredClass " + requiredclass + " on " + $(this).attr('name'));
                if ($(this).hasClass(requiredclass)) {
                    // Reactivate all inputs of the required class.
                    $(this).prop('disabled', null);
                }
            });
            $('#mform1 input[type="submit"]').prop('disabled', null);
            $('#mform1 input[type="hidden"]').prop('disabled', null);

            // finally hide all disabled fitems.
            $('#mform1 .fitem').css('display', 'block');
            $('#mform1 [disabled]').parents('.fitem').css('display', 'none');
        }
    };

    return boardzadminupdate;
});