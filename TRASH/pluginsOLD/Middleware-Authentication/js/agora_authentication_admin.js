jQuery(function(){
	attach_event_handlers();
});

function process_ajax_request(form_data, container){
	jQuery.ajax({
		type: 'POST',
		url: agora_middleware_authentication.ajaxurl,
		data: form_data,
		dataType: "html",
		success: function(data, textStatus, XMLHttpRequest){
			jQuery(container).html(data);
			attach_event_handlers();
            jQuery('.ajax_spinner').hide();
            jQuery('.submit_field input[type="submit"]').show();
            if(data.substr(data.length -4) == 'Pass'){
                jQuery('.ajax_message').show();
                jQuery('.ajax_message').html('Authentication code created successfully');
                jQuery('.ajax_message').addClass('ajax_success');
            } else if(data.substr(data.length -4) == 'Fail')  {
                jQuery('.ajax_message').show();
                jQuery('.ajax_message').html('Failed to create Authentication code');
                jQuery('.ajax_message').addClass('ajax_fail');
            }
        },
		error: function(){
            jQuery('.ajax_message').html('Failed to create Authentication code');
        }
	});
}

function ajax_delete_auth_object(object_id, object_type, container, nonce, parent){
	jQuery.ajax({
		type: 'POST',
		url: agora_middleware_authentication.ajaxurl,
		data: { action: object_type + '_delete', id: object_id, security: nonce, parent: parent },
		dataType: "html",
		success: function(data, textStatus, XMLHttpRequest){
			jQuery(container).html(data);
			attach_event_handlers();
        },
		error: function(){ }
	});
}

function get_rule_form(targetContainer, authcode_id, prepend_to){
    jQuery.ajax({
        type: 'POST',
        url: agora_middleware_authentication.ajaxurl,
        data: {
            action: 'get_rule_form',
            target_container: targetContainer,
            authcode_id: authcode_id
        },
        dataType: 'html',
        success: function(data, textStatus, XMLHttpRequest){
            prepend_to.before(data);
            attach_event_handlers();
        }
    });
}

function attach_event_handlers(){
    // Cleanup bindings. Since we add them multiple times after AJAX requests
    jQuery('.edit_link').unbind('click');
    jQuery('.delete_item').unbind('click');
    jQuery('.add_rule').unbind('click');
    jQuery('.cancel_rule_form').unbind('click');
    jQuery('.cancel_authcode_edit').unbind('click');
    jQuery('#pubcodes_admin form').unbind('submit');
    jQuery('.pubcode_ajax_form.update .auth_type').unbind('change');
    jQuery('.authcode_field input').unbind('focus');

    jQuery('.add_rule').click(function(){
        var prepend_to = jQuery(this);
        var target_container = jQuery(this).data('container');
        var authcode_id = jQuery(this).data('authcode');
        get_rule_form(target_container, authcode_id, prepend_to);
        return false;
    });

    jQuery('.authcode_input').focus(function() {
        jQuery(this).next('.tooltip').fadeIn();
    });

    jQuery('.authcode_input').blur(function() {
        jQuery(this).next('.tooltip').fadeOut();
    });

    jQuery('#pubcodes_admin form').on('submit', function(e){
        jQuery('.submit_field input[type="submit"]').hide();
        jQuery(this).find('.ajax_spinner').show();
        jQuery('.ajax_message').fadeOut();
        jQuery('.ajax_message').removeClass('ajax_success');
        jQuery('.ajax_message').removeClass('ajax_fail');
        var container = '#' + jQuery(this).data('container');
        var disabled = jQuery(this).find(':input:disabled').removeAttr('disabled');
        var form_data = jQuery(this).serialize();
        disabled.attr('disabled','disabled');
        process_ajax_request(form_data, container);
        return false;
    });

	jQuery('.edit_link').click(function(){
		var row_id = jQuery(this).data('row-id');
		jQuery('#' + row_id).toggle();
		return false;
	});

	jQuery('.cancel_authcode_edit').click(function(){
		jQuery(this).closest('tr').toggle('300');
		return false;
	});

    jQuery('.cancel_rule_form').click(function(){
        var form_object = jQuery(this).data('cancel');
        jQuery('#' + form_object).remove();
    });

	jQuery('.delete_item').click(function(){
		var object_id = jQuery(this).data('object-id');
        var object_type = jQuery(this).data('object-type');
        var parent = jQuery(this).data('parent');
        var nonce = jQuery(this).data('nonce');
        var container = '#all_pubcodes_rows';
        var confirm_delete = confirm('Are you sure you want to delete this ' + object_type + '?');

        if(confirm_delete == true){
            ajax_delete_auth_object(object_id, object_type, container, nonce, parent);
        }
		return false;
	});

    jQuery('.pubcode_ajax_form.update .auth_type').change(function(){
        alert('Warning: If you change the Type of an authentication code any rules associated with it will need to be changed too.');
    });

}

