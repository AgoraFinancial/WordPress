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