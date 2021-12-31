jQuery(document).ready(function() {

    jQuery("#send_code").click(function() {

        jQuery.ajax(ajax_object.ajax_url, {
            data: jQuery('form[name="form"]').serialize(),
            success: function(data, textStatus, jqXHR) {

                jQuery('#output').empty().append(data);
            }
        });
    });
});