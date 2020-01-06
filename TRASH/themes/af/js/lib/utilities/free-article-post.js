function freeArticlePost()
{

    var browser = $(window);
    var buttons = $('.post-actions--free');
    var sidebar = $('.aside');
    var featured = $('.related-article');
    var headerHeight = $('.site-header').height() + 20;
    var buttonsPosition = buttons.offset().top;

    if($('body').hasClass('afr-member')) {
        headerHeight = headerHeight - 30;
    }

    console.log(headerHeight);
    console.log(buttonsPosition);
    console.log(browser.scrollTop());

    setButtonsWidth();
    featured.addClass('hidden-content--slide');

    browser.resize(setButtonsWidth);

    browser.on('scroll', function() {

        if(browser.scrollTop() < buttonsPosition - headerHeight) {
            buttons.removeClass('sticky');
        } else {
            buttons.addClass('sticky');
        }

        if(browser.scrollTop() < sidebar.offset().top + sidebar.height() - buttons.height() - headerHeight) {
            buttons.removeClass('end-scroll');
        } else {
            buttons.addClass('end-scroll');
        }

        if(browser.scrollTop() > featured.offset().top - (browser.height() * .75)) {
            featured.removeClass('hidden-content--slide');
        }

    });

    function setButtonsWidth() {
        buttons.css('width', sidebar.width());
    }

}