/*!
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2006, 2014 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD (Register as an anonymous module)
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		// Node/CommonJS
		module.exports = factory(require('jquery'));
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {

	var pluses = /\+/g;

	function encode(s) {
		return config.raw ? s : encodeURIComponent(s);
	}

	function decode(s) {
		return config.raw ? s : decodeURIComponent(s);
	}

	function stringifyCookieValue(value) {
		return encode(config.json ? JSON.stringify(value) : String(value));
	}

	function parseCookieValue(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape...
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}

		try {
			// Replace server-side written pluses with spaces.
			// If we can't decode the cookie, ignore it, it's unusable.
			// If we can't parse the cookie, ignore it, it's unusable.
			s = decodeURIComponent(s.replace(pluses, ' '));
			return config.json ? JSON.parse(s) : s;
		} catch(e) {}
	}

	function read(s, converter) {
		var value = config.raw ? s : parseCookieValue(s);
		return $.isFunction(converter) ? converter(value) : value;
	}

	var config = $.cookie = function (key, value, options) {

		// Write

		if (arguments.length > 1 && !$.isFunction(value)) {
			options = $.extend({}, config.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setMilliseconds(t.getMilliseconds() + days * 864e+5);
			}

			return (document.cookie = [
				encode(key), '=', stringifyCookieValue(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}

		// Read

		var result = key ? undefined : {},
			// To prevent the for loop in the first place assign an empty array
			// in case there are no cookies at all. Also prevents odd result when
			// calling $.cookie().
			cookies = document.cookie ? document.cookie.split('; ') : [],
			i = 0,
			l = cookies.length;

		for (; i < l; i++) {
			var parts = cookies[i].split('='),
				name = decode(parts.shift()),
				cookie = parts.join('=');

			if (key === name) {
				// If second argument (value) is a function it's a converter...
				result = read(cookie, value);
				break;
			}

			// Prevent storing a cookie that we couldn't decode.
			if (!key && (cookie = read(cookie)) !== undefined) {
				result[name] = cookie;
			}
		}

		return result;
	};

	config.defaults = {};

	$.removeCookie = function (key, options) {
		// Must not alter options, thus extending a fresh object...
		$.cookie(key, '', $.extend({}, options, { expires: -1 }));
		return !$.cookie(key);
	};

}));

/*!
 * @copyright Copyright (c) 2017 IcoMoon.io
 * @license   Licensed under MIT license
 *            See https://github.com/Keyamoon/svgxuse
 * @version   1.2.6
 */
/*jslint browser: true */
/*global XDomainRequest, MutationObserver, window */
(function () {
    "use strict";
    if (typeof window !== "undefined" && window.addEventListener) {
        var cache = Object.create(null); // holds xhr objects to prevent multiple requests
        var checkUseElems;
        var tid; // timeout id
        var debouncedCheck = function () {
            clearTimeout(tid);
            tid = setTimeout(checkUseElems, 100);
        };
        var unobserveChanges = function () {
            return;
        };
        var observeChanges = function () {
            var observer;
            window.addEventListener("resize", debouncedCheck, false);
            window.addEventListener("orientationchange", debouncedCheck, false);
            if (window.MutationObserver) {
                observer = new MutationObserver(debouncedCheck);
                observer.observe(document.documentElement, {
                    childList: true,
                    subtree: true,
                    attributes: true
                });
                unobserveChanges = function () {
                    try {
                        observer.disconnect();
                        window.removeEventListener("resize", debouncedCheck, false);
                        window.removeEventListener("orientationchange", debouncedCheck, false);
                    } catch (ignore) {}
                };
            } else {
                document.documentElement.addEventListener("DOMSubtreeModified", debouncedCheck, false);
                unobserveChanges = function () {
                    document.documentElement.removeEventListener("DOMSubtreeModified", debouncedCheck, false);
                    window.removeEventListener("resize", debouncedCheck, false);
                    window.removeEventListener("orientationchange", debouncedCheck, false);
                };
            }
        };
        var createRequest = function (url) {
            // In IE 9, cross origin requests can only be sent using XDomainRequest.
            // XDomainRequest would fail if CORS headers are not set.
            // Therefore, XDomainRequest should only be used with cross origin requests.
            function getOrigin(loc) {
                var a;
                if (loc.protocol !== undefined) {
                    a = loc;
                } else {
                    a = document.createElement("a");
                    a.href = loc;
                }
                return a.protocol.replace(/:/g, "") + a.host;
            }
            var Request;
            var origin;
            var origin2;
            if (window.XMLHttpRequest) {
                Request = new XMLHttpRequest();
                origin = getOrigin(location);
                origin2 = getOrigin(url);
                if (Request.withCredentials === undefined && origin2 !== "" && origin2 !== origin) {
                    Request = XDomainRequest || undefined;
                } else {
                    Request = XMLHttpRequest;
                }
            }
            return Request;
        };
        var xlinkNS = "http://www.w3.org/1999/xlink";
        checkUseElems = function () {
            var base;
            var bcr;
            var fallback = ""; // optional fallback URL in case no base path to SVG file was given and no symbol definition was found.
            var hash;
            var href;
            var i;
            var inProgressCount = 0;
            var isHidden;
            var Request;
            var url;
            var uses;
            var xhr;
            function observeIfDone() {
                // If done with making changes, start watching for chagnes in DOM again
                inProgressCount -= 1;
                if (inProgressCount === 0) { // if all xhrs were resolved
                    unobserveChanges(); // make sure to remove old handlers
                    observeChanges(); // watch for changes to DOM
                }
            }
            function attrUpdateFunc(spec) {
                return function () {
                    if (cache[spec.base] !== true) {
                        spec.useEl.setAttributeNS(xlinkNS, "xlink:href", "#" + spec.hash);
                        if (spec.useEl.hasAttribute("href")) {
                            spec.useEl.setAttribute("href", "#" + spec.hash);
                        }
                    }
                };
            }
            function onloadFunc(xhr) {
                return function () {
                    var body = document.body;
                    var x = document.createElement("x");
                    var svg;
                    xhr.onload = null;
                    x.innerHTML = xhr.responseText;
                    svg = x.getElementsByTagName("svg")[0];
                    if (svg) {
                        svg.setAttribute("aria-hidden", "true");
                        svg.style.position = "absolute";
                        svg.style.width = 0;
                        svg.style.height = 0;
                        svg.style.overflow = "hidden";
                        body.insertBefore(svg, body.firstChild);
                    }
                    observeIfDone();
                };
            }
            function onErrorTimeout(xhr) {
                return function () {
                    xhr.onerror = null;
                    xhr.ontimeout = null;
                    observeIfDone();
                };
            }
            unobserveChanges(); // stop watching for changes to DOM
            // find all use elements
            uses = document.getElementsByTagName("use");
            for (i = 0; i < uses.length; i += 1) {
                try {
                    bcr = uses[i].getBoundingClientRect();
                } catch (ignore) {
                    // failed to get bounding rectangle of the use element
                    bcr = false;
                }
                href = uses[i].getAttribute("href")
                        || uses[i].getAttributeNS(xlinkNS, "href")
                        || uses[i].getAttribute("xlink:href");
                if (href && href.split) {
                    url = href.split("#");
                } else {
                    url = ["", ""];
                }
                base = url[0];
                hash = url[1];
                isHidden = bcr && bcr.left === 0 && bcr.right === 0 && bcr.top === 0 && bcr.bottom === 0;
                if (bcr && bcr.width === 0 && bcr.height === 0 && !isHidden) {
                    // the use element is empty
                    // if there is a reference to an external SVG, try to fetch it
                    // use the optional fallback URL if there is no reference to an external SVG
                    if (fallback && !base.length && hash && !document.getElementById(hash)) {
                        base = fallback;
                    }
                    if (uses[i].hasAttribute("href")) {
                        uses[i].setAttributeNS(xlinkNS, "xlink:href", href);
                    }
                    if (base.length) {
                        // schedule updating xlink:href
                        xhr = cache[base];
                        if (xhr !== true) {
                            // true signifies that prepending the SVG was not required
                            setTimeout(attrUpdateFunc({
                                useEl: uses[i],
                                base: base,
                                hash: hash
                            }), 0);
                        }
                        if (xhr === undefined) {
                            Request = createRequest(base);
                            if (Request !== undefined) {
                                xhr = new Request();
                                cache[base] = xhr;
                                xhr.onload = onloadFunc(xhr);
                                xhr.onerror = onErrorTimeout(xhr);
                                xhr.ontimeout = onErrorTimeout(xhr);
                                xhr.open("GET", base);
                                xhr.send();
                                inProgressCount += 1;
                            }
                        }
                    }
                } else {
                    if (!isHidden) {
                        if (cache[base] === undefined) {
                            // remember this URL if the use element was not empty and no request was sent
                            cache[base] = true;
                        } else if (cache[base].onload) {
                            // if it turns out that prepending the SVG is not necessary,
                            // abort the in-progress xhr.
                            cache[base].abort();
                            delete cache[base].onload;
                            cache[base] = true;
                        }
                    } else if (base.length && cache[base]) {
                        setTimeout(attrUpdateFunc({
                            useEl: uses[i],
                            base: base,
                            hash: hash
                        }), 0);
                    }
                }
            }
            uses = "";
            inProgressCount += 1;
            observeIfDone();
        };
        var winLoad;
        winLoad = function () {
            window.removeEventListener("load", winLoad, false); // to prevent memory leaks
            tid = setTimeout(checkUseElems, 0);
        };
        if (document.readyState !== "complete") {
            // The load event fires when all resources have finished loading, which allows detecting whether SVG use elements are empty.
            window.addEventListener("load", winLoad, false);
        } else {
            // No need to add a listener if the document is already loaded, initialize immediately.
            winLoad();
        }
    }
}());

function accordion()
{

    $('.accordion-header').click(function(e){
        $(this).parent().toggleClass('open');
        $(this).next('div').slideToggle();
    });

}
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

function archiveNav()
{

    $('.archive-year-header').click(function(e){
        $(this).toggleClass('active');
        $(this).next('.archive-month-list').slideToggle();
    });

}
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
/**
 * Require at least one checkbox for coreg page
 * @return {null}
 */
function coReg() {

    // Check at page load
    coRegCheckboxes();

    // Check on change
    $('#LeadGen .coreg-checkbox-input').on('click', function() {
        coRegCheckboxes();
    });
}


/**
 * Check all inputs and start count for all valid
 * @return {[type]} [description]
 */
function coRegCheckboxes() {

    // Reset index
    var i = 0;

    // Get count of all checkboxes
    $('#LeadGen .coreg-checkbox-input').each(function( index ) {
        i++;
        if($(this).is(':checked')) {
            i++;
        } else {
            i--;
        }
    });

    // If at least one checked, allow submission
    if(i > 0) {
        $('#form-submit').removeAttr('disabled').css('opacity','1');
    } else {
        $('#form-submit').attr('disabled', 'disabled').css('opacity','.3');
    }

}
function EmailValidationPost(form, email, ListID, ListName, PubCode, msgError, msgSuccess, msgFormat, msgList){

    // Set message defaults
    msgError = msgError || 'There was a problem with your request, please contact customer service for further assistance.';
    msgSuccess = msgSuccess || 'Success! You will be redirected shortly...';
    msgFormat = msgFormat || 'Your email is formatted incorrectly. Please verify your email address or call customer service for further assistance.';
    msgList = msgList || 'Thank you for your interest, but our records indicate that you are already a subscriber.';
    
    // Remove current message
    $('.eo-message').remove();

    // Set submit button to disabled
    $(form).find('input[type=submit]').attr('disabled', 'disabled').css('opacity','.3');

    // Get source from form for ID
    var source = $(form).find('input[name=Source]').val();

    // Set JSON request
    var requestJson = { 'ListId':ListID, 'Email':email, 'CustomId':source };

    $.ajax('https://api.emailoversight.com/api/emailvalidation', {
        type: 'POST',
        data: JSON.stringify(requestJson),
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        timeout: 3000,
        headers: GetHeaders(),
        success: function (data) {

            console.log('success');
            console.log(data);
           
            var str = JSON.stringify(data, null, 2);
            var obj = JSON.parse(str);

            console.log(data.ResultId);

            var text;
            var result = data.ResultId;

            switch (result) {
                case 1: // Success
                        
                        // Final check against CARL
                        if(ListName) {

                            checkEmailList(form, email, ListName, function(success) {
                                
                                // If on list, display message
                                if (success === true) {

                                    // Assign cookie if already has list
                                    setSignupCookie(PubCode);

                                    // Display message if already signed up
                                    $('<p class="eo-message eo-message--success callout success">' + msgList + '</p>').appendTo($(form));

                                    // Dispatch event hook
                                    var eventLeadGenHasEmail = new CustomEvent('eventLeadGenHasEmail', {detail:form});
                                    document.dispatchEvent(eventLeadGenHasEmail);

                                } else {
                                    // Send form
                                    submitLeadGenForm(form, email, PubCode, msgSuccess);                                
                                }

                            });
                        } else {
                            
                            // Send form
                            submitLeadGenForm(form, email, PubCode, msgSuccess);
                        }

                    break;
                case 5: // Malformed
                        $('<p class="eo-message eo-message--warning callout warning">' + msgFormat + '</p>').appendTo($(form));
                        $(form).find('input[type=submit]').removeAttr('disabled').css('opacity','1');
                    break;
                default:
                        $('<p class="eo-message eo-message--error callout alert">' + msgError + '</p>').appendTo($(form));
                        $(form).find('input[type=submit]').removeAttr('disabled').css('opacity','1');
                    break;
            }

        },
        error: function (jqxhr, textStatus, error) {

            // Display error message
            $('<p class="eo-message eo-message--fail callout alert">' + msgError + '</p>').appendTo($(form));
           
            // Enable submit button
            $(form).find('input[type=submit]').removeAttr('disabled').css('opacity','1');

        }
    });
}
  
function GetHeaders(){
    return { 'ApiToken': 'd735d6d3-7817-474e-889c-1aa525ecd9df' };
}

function forceUniqueValues(array) {
    var result = [];
    $.each(array, function(i, e) {
        if ($.inArray(e, result) == -1) result.push(e);
    });
    return result;
}

function validateNewsletterForm(){
    
    // Get previous cookies
    var rCookie = getCookie('r'),
        pubcookie = '';

    // Build new cookie
    $('#LeadGen *').filter('input:checked').each(function(){
        pubcookie = pubcookie+'_'+($(this).data('pubcode'));
    });
    pubcookie = pubcookie.replace(/^_/, '');

    // Compare previous and new cookie
    var oldArr = rCookie.split('_'),
        newArr = pubcookie.split('_');

    // Build new array
    $.merge(newArr,oldArr);
    newArr = forceUniqueValues(newArr);
    pubcookie = newArr.join('_');

    // Set cookie    
    var d = new Date();
    d.setTime(d.getTime() + (365*24*60*60*1000));
    document.cookie = 'r='+pubcookie+'; expires='+d.toUTCString()+'; path=/';
}

function setSignupCookie(pubCode){

    if(pubCode == '') return;

    // Get incoming pubcode and current r cookie
    var rCookie = getCookie('r'),
        newCookie = '';

    // If has r cookie, check if new is already included
    if(rCookie) {
        var cookieArr = rCookie.split('_');
        var hasCookie = $.inArray( pubCode, cookieArr );

        // It not in current cookie, append
        if(hasCookie < 0) {
            newCookie = rCookie + '_' + pubCode;
        } else {
            newCookie = rCookie;
        }

    } else {
        newCookie = pubCode;
    }

    var d = new Date();
    d.setTime(d.getTime() + (365*24*60*60*1000));
    document.cookie = 'r='+newCookie+'; expires='+d.toUTCString()+'; path=/';

    return;
}

function getCookie(cname){
    var name = cname + '=';
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return '';
}

function checkEmailList(form, email, ListName, callback) {

    $.ajax({
        type : "post",
        dataType : "json",
        url : themeAjaxUrl,
        timeout : 3000,
        data : {action: "listNameHasEmail", email : email, listname: ListName},        
        success: function(response) {
            console.log("success");
            callback(response);
        },
        error: function(error) {
            console.log("error");
            console.log(error);
            
            // If time out, process anyway
            if(error.statusText == "timeout") {
                console.log("Timeout");
                callback(false);
            }
        }, 

    })

}

function submitLeadGenForm(form, email, PubCode, msgSuccess) {

    $('<p class="eo-message eo-message--success callout success">' + msgSuccess + '</p>').appendTo($(form));

    // Send email to lytics on signup
    if(typeof jstag != "undefined") {
        window.jstag.send({ email: email });
    }
    
    // Update/append r cookie based on incoming pubcode
    setSignupCookie(PubCode);

    // Dispatch event hook
    var eventLeadGenSubmitForm = new Event('eventLeadGenSubmitForm');
    document.dispatchEvent(eventLeadGenSubmitForm);

    form.submit();
}
function exitPop()
{

    if($.cookie('exitpop') || $.cookie('r')) {
        return;
    }

    setTimeout(function(){
        windowMouseLeave();
    },8000);

    function addEvent(obj, evt, fn) 
    {
        if (obj.addEventListener) {
            obj.addEventListener(evt, fn, false);
        }
        else if (obj.attachEvent) {
            obj.attachEvent("on" + evt, fn);
        }
    }

    function windowMouseLeave()
    {   
        var hoverOut = false;

        addEvent(document, "mouseout", function(e) {
            e = e ? e : window.event;
            var from = e.relatedTarget || e.toElement;
            if (!from || from.nodeName == "HTML") {

                if (!hoverOut) {
                    hoverOut = true;
                    assembleModal();
                    setExitPopCookie(); // Currently set 
                }
            }
        });
    }

    function setExitPopCookie()
    {
        $.cookie('exitpop', '1', { expires: 7, path: '/' });
    }

    function assembleModal()
    {   
        $('body.openx-active #exitpop').foundation('open');
    }
 
}
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
// Get date format
function getDateFormat(timestamp, format)
{
    var a = new Date(timestamp),
        months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        year = a.getFullYear(),
        month = months[a.getMonth()],
        date = a.getDate(),
        hour = a.getHours(),
        min = a.getMinutes(),
        sec = a.getSeconds(),
        output = month + ' ' + date + ', ' + year;

    switch (format) {
        case 'mdy': output = month + ' ' + date + ', ' + year; break;
        case 'my': output = month + ' ' + year; break;
    }

    return output;
}

// Add zeros when missing for second decimal place
function addPriceZeros( num ) {
    var value = Number(num);
    var res = String(num).split(".");
    if(res.length == 1 || (res[1].length < 3)) {
        value = value.toFixed(2);
    }
    return value
}

// Affect elements when loading ajax
var ajax_loading = jQuery('#ajax-loading');      // Hide/show processing gif
var ajax_button = jQuery('.ajax-button');        // Disable/fade button
jQuery(document)
    .ajaxStart(function () {
        ajax_loading.show();
        ajax_button.attr('disabled', 'disabled').css('opacity','.3');
    })
    .ajaxStop(function () {
        ajax_loading.hide();
        ajax_button.removeAttr('disabled').css('opacity','1');
    });

// Target headers with the attribute sort-column
function sortTableInit() {
    $('.table-sortable th[sort-column]').on('click tap',function(e) {
        n = $(this).attr('sort-column');
        table = $(this).closest('table');
        table = table[0];
        sortTable(n, table);
    })
}

// Row sorting action
function sortTable(n, table) {
    var rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    //table = document.getElementById("portfolio-table");
    switching = true;
    //Set the sorting direction to ascending:
    dir = "asc"; 
    /*Make a loop that will continue until
    no switching has been done:*/
    while (switching) {
        //start by saying: no switching is done:
        switching = false;
        rows = table.rows;
        /*Loop through all table rows (except the
        first, which contains table headers):*/
        for (i = 1; i < (rows.length - 1); i++) {
            //start by saying there should be no switching:
            shouldSwitch = false;
            /*Get the two elements you want to compare,
            one from current row and one from the next:*/
            x = rows[i].getElementsByTagName("TD")[n];
            y = rows[i + 1].getElementsByTagName("TD")[n];
            /* if we encounter a header row, skip it*/
            if(x == undefined || y == undefined) {
                continue;
            }
            /*check if the two rows should switch place,
            based on the direction, asc or desc:*/
            if (dir == "asc") {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    //if so, mark as a switch and break the loop:
                    shouldSwitch = true;
                    break;
                }
            } else if (dir == "desc") {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    //if so, mark as a switch and break the loop:
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            /*If a switch has been marked, make the switch
            and mark that a switch has been done:*/
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            //Each time a switch is done, increase this count by 1:
            switchcount ++;      
        } else {
            /*If no switching has been done AND the direction is "asc",
            set the direction to "desc" and run the while loop again.*/
            if (switchcount == 0 && dir == "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }
}

// Initialize on load
window.onload = sortTableInit;
function loginModal()
{

    // Set vars
    var login_toggle = $('.login-trigger'),
        login_modal = $('.login-modal'),
        login_modal_wrap = $('.login-modal-wrap'),
        login_close = $('.login-modal .login-close'),
        login_form = $('.tfs-mw-wrapper-block form'),
        login_message = $('.tfs-mw-wrapper-block-messages p:not(.tfs-mw-wrapper-block-subtitle)'),
        switcher = $('.tfs-mw-wrapper-block-forgot-username a');


    // Show login modal
    login_toggle.on('click tap', function(e){
        e.preventDefault();
        login_modal.fadeToggle();
    });

    // Close login modal on overlay click
    login_close.on('click tap', function(e){
        e.preventDefault();
        login_modal.fadeToggle();
    });

    // Close login modal
    login_modal.on('click tap', function(e){
        e.preventDefault();
        login_modal.fadeToggle();
    });

    // Prevent close on form
    login_modal_wrap.on('click tap', function(e){
        e.stopPropagation();
    });

    // When retyping password, remove message wrapper
    login_form.keyup(function() {
        login_message.hide();
    });

    // Switching between lost password/username clear message wrapper
    switcher.on('click tap', function(e){
        login_message.hide();
    });

}
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
function searchToggle()
{

    $('.search-toggle').click(function(){
        $('.search-bar').slideToggle();
        $('.search-input').focus();
    })

}
function loadWatchlistPage( user_id, user_token, has_tickers ){

    // Check for params
    if(!user_id) return false;
    if(!user_token) return false;

    // Set vars
    var ticker_input = $('input#addTicker'),
        watchlist_instructions = $('#watchlist-instructions'),
        watchlist_table = $('#watchlist-table');

    // Preload table with tickers only
    if (has_tickers) {
        watchlist_instructions.hide();
        loadWatchlistTable();
    }

    $('#btnAddTicker').click(function(e) {
        e.preventDefault();
        var tickerSymbol = ticker_input.val();
        ticker_input.removeClass('error-doesnt-exist');

        // Load table if currently empty
        if (watchlist_table.html().length == 0) {
            loadWatchlistTable();    
        }

        $.post(themeAjaxUrl,
            {
                action: 'user_data_ajax',
                method: 'addTicker',
                uid: user_id,
                token: user_token,
                val: tickerSymbol
            },
            function(r) {
                r = JSON.parse(r);
                if (r) {
                    if (r[0].Name){                        
                        watchlist_table.show();
                        watchlist_instructions.hide();
                        ticker_input.attr('placeholder','Enter Ticker Symbol');
                        loadWatchlistRow(r[0]);
                    }
                } else {
                    ticker_input.addClass('error-doesnt-exist');
                    var errormessage = '<div class="callout ticker-doesnt-exist alert small">This Ticker Does Not Exist. Please Try Again.<br><strong>NOTE:</strong> The watchlist does not track OTC stocks.</div>';
                    $(errormessage).appendTo($(".watchlist-form")).fadeIn('fast', function() {
                        childElm = $(this);
                        setTimeout(function() {
                            childElm.fadeOut('slow');
                            $("input#addTicker").removeClass('error-doesnt-exist');
                        }, 4000);
                    });
                }
            }
        );
    });

    // Expand ticker content
    $(document).on('click tap','a.ticker_chart_more.expands', function(e) {
        e.preventDefault();
        $(e.target).removeClass('expands');
        watchlist_instructions.hide();
        var tickerSymbol = $(e.target).data('symbol');
        $.post(themeAjaxUrl,
            {
                action: 'user_data_ajax',
                method: 'getTickerContent',
                uid: user_id,
                token: tickerSymbol
            },
            function(r) {
                r = buildWatchlistIframe(tickerSymbol) + r;
                $(e.target).attr('style', '').addClass('collapse').html('&minus; View Less');
                $(e.target).closest('tr').after('<tr class="' + tickerSymbol + ' watchlist-expand-wrap"><td colspan="5">' + r + '</td></tr>');
            }
        );
    });

    // Collapse ticker content
    $(document).on('click tap', 'a.ticker_chart_more.collapse', function(e) {
        e.preventDefault();
        var tickerSymbol = $(e.target).data('symbol');
        $(e.target).removeClass('collapse').addClass('expands').html('&plus; View More');
        $(e.target).closest('tr').next().remove();
    });

    // Remove ticker row
    $(document).on('click tap', 'a.ticker_chart_remove', function(e) {
        e.preventDefault();
        var tickerSymbol = $(e.target).data('symbol');
        watchlist_instructions.hide();
        $.post(themeAjaxUrl,
            {
                action: 'user_data_ajax',
                method: 'removeTicker',
                uid: user_id,
                token: user_token,
                val: tickerSymbol
            },
            function(r) {
                loadWatchlistTable();
            }
        );
    });

    // Build iframe html
    function buildWatchlistIframe( symbol ) {
        if (!symbol) return;
        var html = '<iframe id="tradingview_b489d" src="https://s.tradingview.com/widgetembed/?symbol=' + symbol + '&interval=D&hidetoptoolbar=1&saveimage=0&toolbarbg=f1f3f6&studies=&hideideas=1&theme=White&style=1&timezone=Etc%2FUTC&studies_overrides=%7B%7D&overrides=%7B%7D&enabled_features=%5B%5D&disabled_features=%5B%5D&locale=en&" width="100%" height="450" frameborder="0" allowtransparency="true" scrolling="no" allowfullscreen=""></iframe>';
        return html;
    }

    // Build watchlist table thead
    function buildWatchlistThead() {
        var html = '';
        html += '<thead>';
            html += '<tr>';
                html += '<th data-label="Symbol" sort-column="0">Symbol</th>';
                html += '<th data-label="Last Price" sort-column="1">Last&nbsp;Price</th>';
                html += '<th data-label="Change" sort-column="2">Change</th>';
                html += '<th data-label="% Change" sort-column="3">%&nbsp;Change</th>';
                html += '<th data-label="Actions" sort-column="4">Actions</th>';
            html += '</tr>';
        html += '</thead>';
        html += '<tbody></tbody>';
        return html;
    }

    // Build watchlist table row
    function buildWatchlistRow( symbol, price, change, percent, color_class ) {
        var html = '';
        html += '<tr>';
            html += '<td data-label="Symbol">' + symbol + '</td>';
            html += '<td data-label="Last Price">$' + addPriceZeros(price) + '</td>';
            html += '<td data-label="Change" class="' + color_class + '">' + change + '</td>';
            html += '<td data-label="% Change" class="' + color_class + '">' + percent + '</td>';
            html += '<td data-label="Actions">';
                html += '<a class="button secondary small ticker_chart_more expands" data-symbol="' + symbol + '">&plus; View More</a>';
                html += '<a class="button alert small ticker_chart_remove" data-symbol="' + symbol + '">&times; Remove</a>';
            html += '</td>';
        html += '</tr>';
        return html;
    }

    // Create watchlist row
    function loadWatchlistRow( obj ) {
        var Symbol = obj.Symbol,
            Price = obj.LastTradePriceOnly,
            Change = obj.Change,
            ChangeinPercent = obj.ChangeinPercent;
        if (Price === null) Price = 'n/a';    
        if (Change === null) Change = 'n/a';    
        if (ChangeinPercent === null) ChangeinPercent = 'n/a';
        if (Change === 0) {
            clsColor = 'ticker-no-change';
        } else if (Change < 0) {
            clsColor = 'ticker-down';
        } else {
            clsColor = 'ticker-up';
        }
        $('#watchlist-table tbody').append(buildWatchlistRow( Symbol, Price, Change, ChangeinPercent, clsColor ));
    }

    // Load watchlist 
    function loadWatchlistTable() {
        $.post(themeAjaxUrl,
            {
                action: 'user_data_ajax',
                method: 'getTickers',
                uid: user_id,
                token: user_token
            },
            function(r) {
                var hasTickers = 0;
                watchlist_table.hide();
                watchlist_table.html(buildWatchlistThead());
                sortTableInit(); // Re-init sorting
                if (r) {
                    r = JSON.parse(r);
                    $.each(r, function(idx,obj) {
                        loadWatchlistRow(obj);
                        hasTickers = 1;
                    });
                }
                if (hasTickers) {
                    watchlist_table.show();
                    watchlist_instructions.hide();
                } else {
                    watchlist_instructions.show();
                }
            }
        );
    }

}

// Add to watchlist button for ticker pages
jQuery(document).on('click tap','#add-to-watchlist', function(e) {
    e.preventDefault();
    var user_id = jQuery(this).data('id');
    var user_token = jQuery(this).data('token');
    var symbol = jQuery(this).data('symbol');
    console.log(user_id);
    console.log(user_token);
    console.log(symbol);
    var button = jQuery(this);
    jQuery.post(themeAjaxUrl,
        {
            action: 'user_data_ajax',
            method: 'addTicker',
            uid: user_id,
            token: user_token,
            val: symbol,
        },
        function(r) {
            console.log('Added');
            if (r) {
                button.remove();
            }
        }
    );
});
function whatsNew()
{

    $('select.whats-new-selector').on('change', function() {
        location = $(this).val();
    });

}