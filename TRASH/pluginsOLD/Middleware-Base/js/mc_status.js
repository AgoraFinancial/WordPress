// Middleware Debug Scripts

jQuery(function() {
// Do the quick Middleware connectivity test as soon as the page loads
    jQuery.ajax({
        type: 'POST',
        url: agora_mc_debug.ajaxurl,
        data: {call: "mc_ping_test", action: "mc_ping_test"},
        success: function (data, textStatus, XMLHttpRequest) {
            if (data.indexOf("Message Central Services") != -1) {
                jQuery('#status_indicator').html('Message Central Connection Established Successfully');
                jQuery('#status_help').html(data);
                jQuery('#status_light').addClass('green');
            } else {
                jQuery('#status_indicator').html('Error: Connection Could not Be verified');
                jQuery('#status_help').html('Check that the Agora firewall has been opened for your IP');
                jQuery('#status_light').addClass('red');
            }
        },
        error: function (object, status, errorMessage) {
            jQuery('#status_indicator').html('There was an error connecting with Message Central');
            jQuery('#status_help').html('Check that the Agora firewall has been opened for your IP');
            jQuery('#status_help').html(status + ' ' + errorMessage);
            jQuery('#status_light').addClass('red');
        }
    });

    jQuery('input').on('change', function() {
        enableSave();
    });

    enableSave();

    jQuery('#associate_list_mc').on('click', function () {
        createMailing();
    });
});

jQuery(document).ready(function(){
    if ( jQuery( 'input[name="agora_core_framework_config_mc[mc_mailing]"]:checked' ).val() == '1') {
        jQuery( '.forgot-password-container').show();
    }

    jQuery( 'input[name="agora_core_framework_config_mc[mc_mailing]"]' ).on('change', function() {
        if( jQuery( 'input[name="agora_core_framework_config_mc[mc_mailing]"]:checked' ).val() == '1') {
            jQuery( '.forgot-password-container').show();
        } else {
            jQuery( '.forgot-password-container').hide();
        }
    });
});

function cbChange(obj) {
    var cbs = document.getElementsByClassName("cb");
    for (var i = 0; i < cbs.length; i++) {
        cbs[i].checked = false;
    }
    obj.checked = true;
}

function enableSave(){
    var enabled = true;

    if(jQuery('input[name="agora_core_framework_config_mc[mc_token]"]').val() == ''){
        enabled = false;
    }

    if(jQuery('.mailing_on:checked').size() > 0 && jQuery('input[name="agora_core_framework_config_mc[mc_list]"]').val() == ''){
        enabled = false;
    }

    if(jQuery('input[name="agora_core_framework_config_mc[mc_orgid]"]').val() == ''){
        enabled = false;
    }

    if(enabled === true) {
        jQuery('input[type=checkbox]').each(function () {
            if (this.checked) {
                jQuery('#submit').removeAttr('disabled');
            }
        });
    } else {
        jQuery('#submit').attr('disabled','disabled');
    }
}

function createMailing() {
    jQuery('#associate_list_mc').attr('disabled', 'disabled');
    jQuery.ajax({
        type: 'POST',
        url: agora_mc_debug.ajaxurl,
        data: {action: "mc_create_mailing",
            listcode: jQuery('input[name="agora_core_framework_config_mc[mc_list]"]').val(),
            mailing: jQuery('input[name="agora_core_framework_config_mc[mc_mailing]"]').val(),
            mailing_id: jQuery('input[name="agora_core_framework_config_mc[mc_mailing_id]"]').val()
        },
        success: function (data) {
            var response = jQuery.parseJSON(data);
            jQuery('.associate_feedback').text(response.message);
            jQuery('.associate_feedback').addClass(response.status);
            jQuery('#associate_list_mc').removeAttr('disabled');
        }
    });
}