function postActions()
{

    window.contentButtons = contentButtonsClass();
    window.contentButtons.init();

    function contentButtonsClass() {

        var fontSizes = ['normal', 'large', 'x-large', 'xx-large'],
            fontSizesTotal = fontSizes.length - 1,
            currentSize = getCurrentFontSize(),
            rememberedSize = '';

        function bindButtons()
        {
            resizeTextToggle();
            printPage();
            saveArticle();
            removeArticle();
        }

        function resizeTextToggle() 
        {
            var resize_btn = $('.button--resize');
            setCurrentFontSize();

            resize_btn.unbind('click');
            resize_btn.on('click', function (e) {
                e.preventDefault();
                rememberedSize = currentSize;
                currentSize += 1;
                if (currentSize > fontSizesTotal) 
                    currentSize = 0;
                setCurrentFontSize();

                afga.send('event', {
                    'eventCategory' : 'Resize Text',
                    'eventAction' : 'Click'
                });
            })
        }

        function printPage()
        {
            var print_btn = $('.button--print');
            
            print_btn.unbind('click');
            print_btn.on('click', function () {
                window.print();
                afga.send('event', {
                    'eventCategory' : 'Print',
                    'eventAction' : 'Click',
                });
            })
        }

        function saveArticle()
        {
            var bookmark_btn = $('.button--save');
            
            bookmark_btn.unbind('click');
            bookmark_btn.on('click', function () {
            
                //call ajax
                var uid = bookmark_btn.data('uid');
                var token = bookmark_btn.data('token');
                var aid = bookmark_btn.data('aid');
                $.post({
                    url: themeAjaxUrl,
                    data: {
                        action: "user_data_ajax",
                        method: "addArticle",
                        uid: uid,
                        token: token,
                        val: aid
                    },
                    dataType: "json",
                    success: function() {
                        afga.send('event', {
                            'eventCategory' : 'Customer Saved Articles',
                            'eventAction' : 'Save',
                            'eventLabel' : window.location.href
                        });
                    },
                    error: function() {
                        afga.send('event', {
                            'eventCategory' : 'Customer Saved Articles',
                            'eventAction' : 'Save Error',
                            'eventLabel' : window.location.href
                        });
                    }
                });
                
                bookmark_btn.addClass('disabled');
                bookmark_btn.find('span').html('Saved');

            });
        }

        function removeArticle()
        {
            var remove_btn = $('.button--remove');
            
            remove_btn.unbind('click');
            remove_btn.on('click', function () {
            
                //call ajax
                var uid = $(this).data('uid');
                var token = $(this).data('token');
                var aid = $(this).data('aid');
                $.post({
                    url: themeAjaxUrl,
                    data: {
                        action: "user_data_ajax",
                        method: "removeArticle",
                        uid: uid,
                        token: token,
                        val: aid
                    },
                    dataType: "json",
                    success: function() {
                        afga.send('event', {
                            'eventCategory' : 'Customer Saved Articles',
                            'eventAction' : 'Remove',
                            'eventLabel' : 'Web Page'
                        });
                    },
                    error: function() {
                        afga.send('event', {
                            'eventCategory' : 'Customer Saved Articles',
                            'eventAction' : 'Remove Error',
                            'eventLabel' : 'Web Page'
                        });
                    }
                });
                
                $(this).addClass('disabled');
                $(this).find('span').html('Removed');

            });
        }

        function setCurrentFontSize () {
            $.cookie('textsize', currentSize, { expires: 7, path: '/' });
            $('html').removeClass(fontSizes[rememberedSize]);
            $('html').addClass(fontSizes[currentSize]);
        }

        function getCurrentFontSize () 
        {
            return (($.cookie('textsize')) ? +$.cookie('textsize') : 0);
        }

        return {
            init: function () {
                bindButtons();
            },
            bindContentMenu: function() {
                bindButtons();
            }
        }
    }

}