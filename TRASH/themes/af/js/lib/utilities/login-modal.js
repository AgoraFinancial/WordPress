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