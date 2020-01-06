<?php if( !empty( $client_id) && !empty($message) && !empty($api_version) && !empty($fb_redir_url) && isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ) { ?>
    <div id="fb-root"></div>
    <div class="fb-center-button">
        <div class="fb-login-button" data-max-rows="1" data-size="large" data-button-type="continue_with" data-show-faces="false" data-auto-logout-link="false" data-use-continue-as="true" onlogin="logUserIn();"></div>
    </div>
    <script>
        function logUserIn() {
            document.cookie = "fb_login_redirect_uri=; expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/";
            document.cookie = "fb_login_redirect_uri=" + window.location + ";path=/";
            window.location.replace("https://www.facebook.com/v<?php echo $api_version ?>/dialog/oauth?client_id=<?php echo $client_id ?>&redirect_uri=<?php echo urlencode($fb_redir_url) ?>&response_type=code&scope=email");
        }
    </script>
<?php } ?>