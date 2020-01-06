function mainNav(breakpoint)
{

    /* 
     * Variables
     */
    
    // Match desired breakpoint
    breakpoint = breakpoint | 860;

    // Declare variables
    var navID = '.tabs--nav',
        navDesktopID = '.tabs--nav.is-desktop',
        navMobileID = '.tabs--nav.is-mobile',
        navSelector = $(navID),
        itemsSelector = $(navID + ' > .tabs-title:not(.more-tab):not(.callout-nav):not(.hide)'),
        itemsSelector1 = $(navID + ' > .tabs-title:not(.callout-nav)'),
        windowWidth = $(window).width(),        
        widthNav = navSelector.width(),
        widthItems = 0,
        resizeWidth,       
        numItems = itemsSelector.length,
        buffer,
        resizeSmaller = true,
        loopCount = 0,
        resizeTimer,
        mobileSubToggled = false,
        isMobile,
        targetMainItem,
        targetSubItem;



    /*
     * Created Elements
     */
    
    // Create .dropdown-trigger for mobile
    $('.main-nav .menu .menu-item-has-children > a').prepend('<div class="dropdown-toggle"></div>');

    // Clone desktop nave and rename as mobile
    $(navDesktopID).clone().removeClass('is-desktop').addClass('is-mobile').insertAfter('.tabs--nav');

    // Append "more-tab" to desktop nav
    $(navDesktopID).append('<li class="tabs-title more-tab hide"><a href="#">More...</a><ul class="tab-sub-menu"></ul></li>');

    // Clone tabs to more tab
    $(navDesktopID + ' > .tabs-title:not(.more-tab):not(.callout-nav)').clone(true,true).appendTo('.tab-sub-menu');

    // Body overlay
    $('body').append('<div class="nav-overlay"></div>');



    /*
     * Triggers
     */

    // Toggle class on nav toggle
    $('.nav-toggle').click(function(){
        $('.nav-overlay').fadeToggle();
        $(this).toggleClass('active');
        $('.main-nav .menu').slideToggle();
    });

    // Toggle on overlay
    $('.nav-overlay').on('click tap',function(e) {
        $(this).fadeToggle();
        $('.nav-toggle').toggleClass('active');
        $('.main-nav .menu').slideToggle();
    });

    // Toggle mobile submenu on on .dropdown-toggle click
    $('.dropdown-toggle').click(function(e){
        e.preventDefault();
        $(this).toggleClass('active');
        $(this).parent().next('ul').slideToggle();
    });

    // Trigger for overflow links on tab nav
    $('.more-tab > a').on('click tap',function(e) {
        e.preventDefault();
        $(this).next('ul').toggle();
        $(this).parent().toggleClass('is-active');
    });
        
    // Control mobile tab nav
    $(document).on('click tap', navMobileID + ' .tabs-title.is-active a', function(e) {
        e.preventDefault();
        if (!mobileSubToggled) {
            $(navMobileID + ' .tabs-title').slideDown();
            $(navMobileID + ' .tabs-title.is-active').addClass('open');
            mobileSubToggled = true;
        } else {
            $(navMobileID + ' .tabs-title').removeClass('open');
            $(navMobileID + ' .tabs-title').not('.is-active').slideUp();
            mobileSubToggled = false;
        }
    });

    // Trigger for AFR toolbar nav
    $('.imprint-link').on('click tap',function(e) {
        e.preventDefault();
        $('.toolbar-links').slideToggle();
    });



    /*
     * On Load
     */

    if($(window).width() >= breakpoint) {
        isMobile = false;

        // Set buffer to colapse overflow links
        buffer = $('.more-tab').width(),
        widthNav = widthNav - buffer;

        // Get width of all nav items
        $(navDesktopID + ' > li').each(function() {
            widthItems += $(this).outerWidth();
        });

        // Run resizeNav to see if we have overflow links
        resizeNav(widthNav, widthItems, resizeSmaller, loopCount);

    } else {
  
        isMobile = true;
    }



    /*
     * On Resize
     */

    $(window).on('resize', function() {

        // Start faux debounce
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {

            if($(window).width() >= breakpoint) {

                isMobile = false;

                // Save current window width
                resizeWidth = $(window).width();

                // Update resized nav width
                widthNav = navSelector.width();
                widthNav = widthNav - buffer;

                // Update current nav items width
                widthItems = 0;
                $(navDesktopID + ' > li').each(function() {
                    widthItems += $(this).outerWidth();
                });

                // Flag direction of resize
                if(windowWidth > resizeWidth) {
                    resizeSmaller = true;
                } else {
                    resizeSmaller = false;
                }

                // Reset loop count to 0
                loopCount = 0;
                resizeNav(widthNav, widthItems, resizeSmaller, loopCount);

                // Update widow with
                windowWidth = resizeWidth;

                // Remove all overflow links if submenu is empty
                if($('.more-tab').find('li.show').length === 0) {
                    console.log('childless');
                    $('.more-tab').addClass('hide');
                } else {
                    console.log('got kids');
                    $('.more-tab').removeClass('hide');
                }

            } else {

                isMobile = true;
            }

        }, 250);

    });



    /*
     * Functions
     */

    //builds the new nav
    function resizeNav(widthNav, widthItems, resizeSmaller, loopCount) {

        // If resizing larger, hide visible overflow items and show in main nav
        if(!resizeSmaller) {
            $('.more-tab li.show').each(function() {
                widthItems += $(this).width();
                if(widthNav > widthItems) {
                    targetMainItem = $(navDesktopID + ' li:not(.callout-nav):nth-child(' + numItems + ')');
                    targetSubItem = $('.more-tab li:nth-child(' + numItems + ')');

                    // Show/hide items as necessary and add to count
                    targetMainItem.removeClass('hide');
                    targetSubItem.removeClass('show');
                    numItems++;
                }
            });
        }

        // If resizing smaller, hide items from main and show in submenu
        if(widthNav <= widthItems) {
            
            // Show more
            $('.more-tab').removeClass('hide');

            if(resizeSmaller) {
                
                // Target and hide main nav item
                targetMainItem = $(navDesktopID + ' li:not(.callout-nav):nth-child(' + numItems + ')');
                targetWidth = targetMainItem.width();
                targetMainItem.addClass('hide');

                // Target and show sub nav item
                targetSubItem = $('.more-tab li:nth-child(' + numItems + ')');
                targetSubItem.addClass('show');

                // Update variables
                widthItems = widthItems - targetWidth;
                widthNav = widthNav;
                numItems--;
            }

            // Arbitrary stop in case someone goes resize crazy
            loopCount++;
            if(loopCount < 5) {
                resizeNav(widthNav, widthItems, resizeSmaller, loopCount);
            }
        }

        return;

    }

}