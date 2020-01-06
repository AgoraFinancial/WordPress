function buttonAnalyticsTracking() {
    $('.button').mousedown(function() {
        var button = $(this);
        var eventCategory = button.attr('data-event-category');
        var eventLabel = button.attr('href');
        if (typeof eventCategory !== typeof undefined && eventCategory !== false && typeof eventLabel !== typeof undefined && eventLabel !== false) {
            afga.send('event', {
                'eventCategory' : eventCategory,
                'eventAction' : 'Click',
                'eventLabel' : eventLabel
            });
        }
    });
}
