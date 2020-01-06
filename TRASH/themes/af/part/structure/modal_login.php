<div class="login-modal">
    <div class="login-modal-wrap">
        <h2>Login</h2>
        <?php echo apply_filters('af_modal_login', do_shortcode( '[agora_middleware_login]' )); ?>
        <span class="login-close">&#x2715;</span>
    </div>
</div>

<?php
$has_error = isset($_SESSION['agora_session_var']['login']['class']) && $_SESSION['agora_session_var']['login']['class'] == 'error' ? true : false;
$login_page = isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] == '/login/' ? true : false ;
if($has_error && !$login_page) echo '<script>document.getElementsByClassName("login-modal")[0].style.display = "block";</script>';
if($has_error && function_exists('w3tc_pgcache_flush')) w3tc_pgcache_flush();
?>