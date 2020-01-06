jQuery(document).ready(function() {
    // Hide returned message if entering new information in inputs.
    jQuery('form[name="import_base_options"] :input').keyup(function() {
        jQuery('.message_import_base').slideUp();
    });

    jQuery('#exported_settings_base').focusin(function() {
        jQuery('#exported_settings_base').select();
    });

    jQuery('#submit-import_base_options').click(function(e) {
        jQuery("[data-remodal-id=import_options_base]").remodal().close();
    });
});