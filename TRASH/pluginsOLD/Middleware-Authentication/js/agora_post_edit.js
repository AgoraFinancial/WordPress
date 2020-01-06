jQuery(function(){
    attach_event_handlers();
});


function attach_event_handlers(){
    jQuery('#shortcodes-all').on('click', '.fstResultItem', function () {
        var shortcodes = Array();
        var cnt = 0;
        jQuery('#shortcodes-all .fstElement .fstControls div').each(function () {
            shortcodes[cnt] = jQuery(this).attr("data-value");
            cnt++;
        });
        jQuery('#pubcodes').val('[hidecontent pubcodes="' + shortcodes + '"]   [/hidecontent]');
        jQuery('#pubcodes').attr('size', jQuery('#pubcodes').val().length );
        jQuery('#pubcodes').select();
    });
    jQuery('#pubcodes').select();
}

jQuery(document).ready(function(){

    jQuery('#pubcode-tabs li a').click(function(e){
        e.preventDefault();
        var tabShow = jQuery(this).attr('href');
        jQuery('#taxonomy-pubcodes .tabs-panel').hide();
        jQuery('#taxonomy-pubcodes .category-tabs li').removeClass('tabs');
        jQuery(this).parent().addClass('tabs');
        jQuery(tabShow).show();
        return false;
    });

    jQuery('.multipleSelect').fastselect();

    jQuery(".mw_shortcode").click(function() {

        try{
            send_to_editor(jQuery(this).attr("data-mws"));
            jQuery("[data-remodal-id=shortcode-picker]").remodal().close();
        } catch(err) {
            jQuery("[data-remodal-id=shortcode-picker]").remodal().close();
        }

        return false;
    });
});