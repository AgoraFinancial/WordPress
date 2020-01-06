function profile()
{

    // Chart display
    $('.results-progress-completion').each(function () {
        $(this).animate({'width' : $(this).data('width') + '%'}, 1000, function () {
            $('.bestmatch').fadeIn('fast');
        });
    })

    // Button tracking
    $('a.button-view-pub, a.title-view-pub').on('click',function(e){
        var pubcode = $(this).data('pubcode');
        afga.send('event', {
            'eventCategory' : 'Financial Profile',
            'eventAction' : 'Click Through',
            'eventLabel' : pubcode
        });
    });
    $('a.button-subscribe').on('click',function(e){
        var pubcode = $(this).data('pubcode');
        afga.send('event', {
            'eventCategory' : 'Financial Profile',
            'eventAction' : 'Click Through Subscribe',
            'eventLabel' : pubcode
        });
    });

}