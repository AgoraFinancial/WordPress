function accordion()
{

    $('.accordion-header').click(function(e){
        $(this).parent().toggleClass('open');
        $(this).next('div').slideToggle();
    });

}