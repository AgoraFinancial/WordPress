function choosePath()
{
    
    //choose path templates tracking
    $('.choose-whats-new').on('click', function () {
        afga.send('event', {
            'eventCategory' : 'Choose Path',
            'eventAction' : 'Whats New',
        });
    })
    $('.choose-list-subs').on('click', function () {
        afga.send('event', {
            'eventCategory' : 'Choose Path',
            'eventAction' : 'Subscriptions List',
        });
    });
    $('.choose-my-account').on('click', function () {
        afga.send('event', {
            'eventCategory' : 'Choose Path',
            'eventAction' : 'My Account',
        });
    });

}