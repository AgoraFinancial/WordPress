function pubPortfolio()
{

    // Default setup
    var filterClass = 'hidden-filter',
        filterButton = '.filter-button',
        openCount = $('.openContent:not(.trade-group-header)').length,
        closeCount = $('.closeContent:not(.trade-group-header)').length;
    $('.editorWatchlist').hide();                        // Hide default rows
    $('#editorWatchlist').addClass(filterClass);         // Add class to "off" buttons

    // Only hide closed if there are open stocks available
    if(openCount > 0) {
        $('.closeContent').hide();
        $('#closeContent').addClass(filterClass);
    }
    // Show close headers if applicable
    else {
        $('.closeContent.trade-group-header').show();
    }

    // Check if open header has any sub items, if not, remove
    $('.trade-group-header').each(function(){
        var headerID = $(this).attr('data-id'),
            headerClass = $(this).attr('class'),
            headerClass = headerClass.replace('trade-group-header ','');
            rowCount = $('.'+headerClass+'[data-id="'+headerID+'"]').length;
        if(rowCount <= 1) {
            $(this).remove();
        }
    });

    // Get id from button and use to find rows and show/hide as needed
    $(filterButton).click(function(e){
        e.preventDefault();
        if($(this).hasClass(filterClass)) {
            $(filterButton).addClass(filterClass);
            $(this).removeClass(filterClass);
        } else {
            return;
        }
        var targetID = $(this).attr('id'),
            showClass = $('.'+targetID),
            hideClass = $('.portfolio-table tbody tr:not(.'+targetID+')'),
            priceLabel = $('.portfolio-table thead th.lastPrice');
        // Change label for last price, depending on open/closed
        if(targetID == 'closeContent' && priceLabel.text() == 'Current Price') {
            priceLabel.text('Exit Price');
        } else if(targetID == 'openContent' && priceLabel.text() == 'Exit Price') {
            priceLabel.text('Current Price');
        }
        showClass.show();
        hideClass.hide();
    });

}