function faq()
{
    // Slow auto scroll effect
    $('a[href*="#"]:not([href="#"])').click(function(){if(location.pathname.replace(/^\//,"")==this.pathname.replace(/^\//,"")&&location.hostname==this.hostname){var a=$(this.hash);if(a=a.length?a:$("[name="+this.hash.slice(1)+"]"),a.length)return $("html, body").animate({scrollTop:a.offset().top},1e3),!1}});

    setAnalytics();

    function setAnalytics()
    {
        answerVote();
        //autoFill();
    }

    function answerVote()
    {
        $('a.faq-helpful').on('click',function(e){
            e.preventDefault();
            e.stopPropagation();
            var question = $(this).parents('div.question').attr('id');
            var pid = $(this).parents('div.question').attr('pid');
            var action = $(this).data('faq-helpful');
            var message = '<p>Thanks for your input. Its nice for us to know we were able to help!</p>';

            if( action == 'yes' ) {
                action = 'Helpful';
            }
            else if( action == 'no' ) {
                action = 'Not Helpful';
                message = '<p>We are sorry we were unable to help you. Please <a href="/contact-us/">contact us</a> and one of our customer service representatives will assist you.</p>';
            }

            afga.send('event', {
                'eventCategory' : 'FAQ Question',
                'eventAction' : action,
                'eventLabel' : '/help/'+question
            });

            $(this).parent().html(message);
            logPopularQuestion(question, pid);
        });
    }   

    /**
     * @param  {string} slug
     * @return {null}
     */
    function logPopularQuestion(slug, pid)
    {
        $.post({
            url: themeAjaxUrl,
            data: {
                action: 'faq_popular_question',
                help_slug: slug,
                post_id: pid
            },
            dataType: 'html'
        });
    }

    /**
     * Copied from old theme
     * @return {null}
     */
    function autoFill()
    {
        var swiftype_results = function(document_type,item){
            var out = '<a href="/help/'+item['permalink']+'/"><p class="title">'+item['title']+'</p></a>';
            return out;
        };

        var af_swiftype = {
            init : function(){
                $('.swiftype_q').each(function(){
                    var engine_key = $(this).data('swiftkey-engine');
                    // the document type to search through
                    var document_type =  $(this).data('swiftkey-document-types');

                    if( $.isArray(document_type) && document_type.length > 0 ){
                        $(this).swiftype({ 
                            engineKey: engine_key,
                            documentTypes: document_type,
                            renderFunction: swiftype_results
                        });
                    }else{
                        $(this).swiftype({ 
                            engineKey: engine_key,
                            renderFunction: swiftype_results
                        });
                    }

                });
            },
            dropdown : function(){
                
                $(document).on('keydown','.swiftype_q',function(e){
                    var swiftype_visible = $('.swiftype-widget .autocomplete').is(':visible')

                    if( e.which == 13 && swiftype_visible ){

                        if( $('.swiftype-widget > .autocomplete > ul > li.active').length > 0 ){
                            afga.send('event', {
                                'eventCategory' : 'FAQ Question',
                                'eventAction' : 'Search Results Path',
                                'eventLabel' : 'Dropdown'
                            });
                        }
                    }
                });

                $(document).on('click', '.swiftype-widget a', function(e){
                    afga.send('event', {
                        'eventCategory' : 'FAQ Question',
                        'eventAction' : 'Search Results Path',
                        'eventLabel' : 'Dropdown'
                    });
                });
            },
            search : function(){
                $('form#swiftype_search').on('submit',function(){
                    afga.send('event', {
                        'eventCategory' : 'FAQ Question',
                        'eventAction' : 'Search Results Path',
                        'eventLabel' : 'Search'
                    });
                });
            }
        };

        //af_swiftype.init();
        //af_swiftype.dropdown();
        //af_swiftype.search();
    }

    return;

    // return {
    //     init: function() {
    //         setAnalytics();
    //     }
    // }
}