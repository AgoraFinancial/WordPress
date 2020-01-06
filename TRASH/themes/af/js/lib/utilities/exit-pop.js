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