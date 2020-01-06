function whatsNew()
{

    $('select.whats-new-selector').on('change', function() {
        location = $(this).val();
    });

}