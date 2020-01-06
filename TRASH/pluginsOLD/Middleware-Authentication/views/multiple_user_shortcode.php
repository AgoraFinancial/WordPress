<?php
$get_sl = $_GET[ 'sl' ];
$get_mu = $_GET[ 'multiple-users' ];

if ( $get_sl ) {
    $get_sl = sanitize_text_field( $get_sl );
}

if ( $get_mu ) {
    $get_mu = esc_attr( $get_mu );
}

// Used for generating unique IDs for labels to work - multiple login forms on the same page.
$form_get_time = mt_rand();
?>

<div class="tfs-mw-wrapper-block-messages">
    <?php
    if ( ! empty( $message ) ) {
        if ( isset( $message ) ) {
            ?>
            <p class="tfs-mw-wrapper-block-subtitle"><?php echo $message; ?></p>
            <?php
        }
        ?>
        <?php
    }
    ?>
</div>


<form action="" method="post" id="tfs-mw-login-form-<?php echo $form_get_time; ?>" class="tfs-mw-loginform">
    <div class="multiple_error_block"></div>
    <input type="hidden" name="multi-data" value="<?php echo $get_mu; ?>">

    <?php
    if( ! empty( $get_sl ) ) {
        ?>
        <input type="hidden" name="secure_link" value="true">
    <?php
    }
    ?>

    <div class="login-username">
        <label for="tfs-mw-user-login_<?php echo $form_get_time; ?>"><?php echo $label_username; ?></label>
        <?php
        if ( is_array( $accounts ) ) {
            ?>
            <select name="multi-username" id="tfs-mw-user-login_<?php echo $form_get_time; ?>">
                <?php
                foreach ( $accounts as $account ) {
                    ?>
                    <option value="<?php echo $account->id->userName; ?>"><?php echo $account->id->userName; ?></option>
                    <?php
                }
                ?>
            </select>
            <?php
        }
        ?>
    </div>

    <div class="login-submit">
        <input type="submit" name="wp-submit" class="button button-primary tfs-mw-wp-submit" value="<?php echo $label_log_in; ?>" data-login-form-id="tfs-mw-login-form-<?php echo $form_get_time; ?>">
    </div>
</form>
