// Do the quick Middleware connectivity test as soon as the page loads
jQuery.ajax({
    type: 'POST',
    url: agora_mw_debug.ajaxurl,
    data: { call: "ping_test", action: "mw_ping_test"},
    success: function(data, textStatus, XMLHttpRequest){
        if(data.indexOf("Middleware Services") != -1){
            jQuery('#status_indicator').html('Middleware Connection Established Successfully');
            jQuery('#status_help').html(data);
            jQuery('#status_light').addClass('green');
        }else{
            jQuery('#status_indicator').html('Error: Connection Could not Be verified');
            jQuery('#status_help').html('Check that the Agora firewall has been opened for your IP');
            jQuery('#status_light').addClass('red');
        }
    },
    error: function(object, status, errorMessage){
        jQuery('#status_indicator').html('There was an error connecting with Middleware');
        jQuery('#status_help').html('Check that the Agora firewall has been opened for your IP');
        jQuery('#status_help').html(status + ' ' + errorMessage);
        jQuery('#status_light').addClass('red');
    }
});