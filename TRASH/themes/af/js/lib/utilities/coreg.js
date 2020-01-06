/**
 * Require at least one checkbox for coreg page
 * @return {null}
 */
function coReg() {

    // Check at page load
    coRegCheckboxes();

    // Check on change
    $('#LeadGen .coreg-checkbox-input').on('click', function() {
        coRegCheckboxes();
    });
}


/**
 * Check all inputs and start count for all valid
 * @return {[type]} [description]
 */
function coRegCheckboxes() {

    // Reset index
    var i = 0;

    // Get count of all checkboxes
    $('#LeadGen .coreg-checkbox-input').each(function( index ) {
        i++;
        if($(this).is(':checked')) {
            i++;
        } else {
            i--;
        }
    });

    // If at least one checked, allow submission
    if(i > 0) {
        $('#form-submit').removeAttr('disabled').css('opacity','1');
    } else {
        $('#form-submit').attr('disabled', 'disabled').css('opacity','.3');
    }

}