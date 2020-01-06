<!--
	It is highly recommended that you move this file to your theme folder.
	Styles should be moved to your main stylesheet too.
	Leave this template in the /theme/ folder of the plugin for future reference

    #Version: 2.0
-->

<?php
$get_t = ( isset( $_GET[ 't' ] ) ? sanitize_text_field( $_GET[ 't' ] ) : '' );
$get_multiple_users = ( isset( $_GET[ 'multiple-users' ] ) ? sanitize_text_field( $_GET[ 'multiple-users' ] ) : '' );
$get_mode = ( isset( $_GET[ 'mode' ] ) ? sanitize_text_field( $_GET[ 'mode' ] ) : '' );

$has_t = get_transient( $get_t );

$multiple_users = get_transient($get_multiple_users);
?>

<div class="tfs-mw-wrapper-block">
    <?php
    if ( $get_multiple_users && ! empty( $multiple_users ) ) {
        if ( ! empty ( $login_title ) ) {
            ?>
            <div id="tfs-mw-wrapper-block-title">
                <h2><?php echo $login_title; ?></h2>
            </div>
            <?php
        }
        ?>

        <?php echo do_shortcode('[multiple_users_shortcode]');
    } else if( $get_t ) {
        if ( isset( $successful ) ) {
            if ( ! empty( $password_reset_successful ) ) {
                ?>
                <p>
                    <?php echo $password_reset_successful; ?>
                </p>
                <?php
            }
            ?>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo $home_link; ?></a>
            <?php
        } else if ( ! empty( $has_t ) ) {
            if ( ! empty ( $change_pw_title ) ) {
                ?>
                <div id="tfs-mw-wrapper-block-title">
                    <h2><?php echo $change_pw_title; ?></h2>
                </div>
                <?php
            }
            ?>

            <div class="tfs-mw-wrapper-block-messages">
                <?php
                if ( ! empty( $password_reset_subtitle ) ) {
                    ?>
                    <p class="tfs-mw-wrapper-block-subtitle">
                        <?php echo $password_reset_subtitle; ?>
                    </p>
                    <?php
                }

                if ( ! empty( $message ) || ! empty( $temp_message ) ) {
                    if ( empty( $message_class ) ) {
                        $message_class = 'error';
                    }

                    if ( isset( $message ) ) {
                        ?>
                        <p class="tfs-mw-wrapper-block-<?php echo $message_class; ?>"><?php echo $message; ?></p>
                        <?php
                    }

                    if ( isset( $temp_message ) ) {
                        ?>
                        <p class="tfs-mw-wrapper-block-error"><?php echo $temp_message; ?></p>
                        <?php
                    }
                    ?>
                    <?php
                }
                ?>
            </div>

            <div id="new-password-form">
                <form name="password_change_form" id="password-change-form" action="" method="post">
                    <input type="hidden" id="passed_email" value="<?php echo $get_email; ?>">
                    <?php
                    if ( $get_mode && $get_mode == 'temp' ) {
                        $user = wp_get_current_user();
                        echo '<input id="username" type="hidden" name="username" value="' . $user->data->user_login .'">';
                    } else {
                        ?>
                        <div class="user-email">
                            <?php
                            if ( ! empty( $new_pwd_username_input_label ) ) {
                                ?>
                                <label for="username"><?php echo $new_pwd_username_input_label; ?></label>
                                <?php
                            }
                            ?>

                            <input id="username" type="text" name="username" placeholder="<?php echo $reset_password_username_placeholder; ?>" value="<?php echo $get_username; ?>" required>
                        </div>
                    <?php
                    }
                    ?>
                    <div class="new-password">
                        <?php
                        if ( ! empty( $new_pw_label ) ) {
                            ?>
                            <label for="new-password"><?php echo $new_pw_label; ?></label>
                            <?php
                        }
                        ?>

                        <div class="mw-password-field-masking-container">
                            <input id="new-password" type="password" name="new-password" placeholder="<?php echo $reset_password_new_password_placeholder; ?>" required>

                            <span class="mw-password-field-masking mw-password-unmask" data-masking-input-id="new-password"></span>
                        </div>
                    </div>

                    <div class="confirm-password">
                        <?php
                        if ( ! empty( $confirm_pw_label ) ) {
                            ?>
                            <label for="confirm-password"><?php echo $confirm_pw_label; ?></label>
                            <?php
                        }
                        ?>

                        <div class="mw-password-field-masking-container">
                            <input id="confirm-password" type="password" name="confirm-password" placeholder="<?php echo $reset_password_confirm_password_placeholder; ?>" required>

                            <span class="mw-password-field-masking mw-password-unmask" data-masking-input-id="confirm-password"></span>
                        </div>
                    </div>

                    <div class="new-password-submit">
                        <input class="mw-reset-password-button" type="submit" name="wp-submit" id="wp-submit" value="<?php echo $change_pw_button; ?>">
                    </div>

                    <?php wp_nonce_field( $nonce_action, $nonce_field ); ?>
                </form>
            </div>
            <?php
        } else {
            // Link has expired
            ?>
            <p>
                <?php echo $invalid; ?>
            </p>
            <?php
        }
    } else {
        // Which form to show when visiting page?
        $password_reset_visibility = '';
        $username_reminder_visibility = '';

        if ( ! empty( $_GET[ 'forgot' ] ) && $_GET[ 'forgot' ] == 'username' ) {
            $password_reset_visibility = 'style="display: none;"';
            $username_reminder_visibility = 'style="display: block!important;"';
        }
        ?>
        <div class="fp-password-section" <?php echo $password_reset_visibility; ?>>
            <?php
            if ( ! empty ( $pw_title ) ) {
                ?>
                <div id="tfs-mw-wrapper-block-title">
                    <h2><?php echo $pw_title; ?></h2>
                </div>
                <?php
            }
            ?>

            <form name="password_reset_form" id="password-reset-form" action="?forgot=password" method="post">
                <input type="hidden" name="mode" value="p">

                <div class="tfs-mw-wrapper-block-messages">
                    <?php
                    if ( ! empty( $subtitle ) ) {
                        ?>
                        <p class="tfs-mw-wrapper-block-subtitle">
                            <?php echo $subtitle; ?>
                        </p>
                        <?php
                    }

                    if ( ! empty( $message ) || ! empty( $temp_message ) ) {
                        if ( empty( $message_class ) ) {
                            $message_class = 'error';
                        }

                        if ( isset( $message ) ) {
                            ?>
                            <p class="tfs-mw-wrapper-block-<?php echo $message_class; ?>"><?php echo $message; ?></p>
                            <?php
                        }

                        if ( isset( $temp_message ) ) {
                            ?>
                            <p class="tfs-mw-wrapper-block-error"><?php echo $temp_message; ?></p>
                        <?php
                        }
                        ?>
                        <?php
                    }
                    ?>
                </div>

                <div class="user-email">
                    <?php
                    if ( ! empty( $email_label ) ) {
                        ?>
                        <label for="user_email"><?php echo $email_label; ?></label>
                        <?php
                    }
                    ?>

                    <input type="email" name="user_email" id="user_email" class="mw-user-email-input input <?php echo isset ( $invalidp ) ? 'invalid' : ''; ?>" value="" size="20" placeholder="<?php echo $forgot_username_password_email_placeholder; ?>" required>
                </div>

                <div class="reset-submit">
                    <input class="mw-forgot-password-button" type="submit" name="wp-submit" id="wp-submit" value="<?php echo $pw_button; ?>">
                </div>

                <?php
                if ( ! empty( $magic_link_button_label ) ) {
                    ?>
                    <div class="tfs-mw-wrapper-block-magic-link">
                        <div class="tfs-mw-wrapper-block-divider">or</div>

                        <a class="mw-forgot-password-button tfs-mw-wrapper-block-magic-link-button">
                            <?php echo $magic_link_button_label; ?>
                        </a>
                    </div>
                    <?php
                }

                wp_nonce_field( $nonce_action, $nonce_field );
                ?>
            </form>

            <?php
            if ( ! empty( $forgot_username_link ) ) {
                ?>
                <div class="tfs-mw-wrapper-block-forgot-username">
                    <a onClick="fp_section_toggle();"><?php echo $forgot_username_link; ?></a>
                </div>
                <?php
            }
            ?>
        </div>





        <div class="fp-username-section" <?php echo $username_reminder_visibility; ?>>
            <?php
            if ( ! empty ( $u_title ) ) {
                ?>
                <div id="tfs-mw-wrapper-block-title">
                    <h2><?php echo $u_title; ?></h2>
                </div>
                <?php
            }
            ?>

            <div id="show-username-form">
                <form name="username_reset_form" id="username-reset-form" action="?forgot=username" method="post">
                    <input type="hidden" name="mode" value="u">

                    <div class="tfs-mw-wrapper-block-messages">
                        <?php
                        if ( ! empty( $u_subtitle ) ) {
                            ?>
                            <p class="tfs-mw-wrapper-block-subtitle">
                                <?php echo $u_subtitle; ?>
                            </p>
                            <?php
                        }

                        if ( ! empty( $message ) || ! empty( $temp_message ) ) {
                            if ( empty( $message_class ) ) {
                                $message_class = 'error';
                            }

                            if ( isset( $message ) ) {
                                ?>
                                <p class="tfs-mw-wrapper-block-<?php echo $message_class; ?>"><?php echo $message; ?></p>
                                <?php
                            }

                            if ( isset( $temp_message ) ) {
                                ?>
                                <p class="tfs-mw-wrapper-block-error"><?php echo $temp_message; ?></p>
                                <?php
                            }
                            ?>
                            <?php
                        }
                        ?>
                    </div>

                    <div class="user-email">
                        <?php
                        if ( ! empty( $email_label ) ) {
                            ?>
                            <label for="user_email"><?php echo $email_label; ?></label>
                            <?php
                        }
                        ?>

                        <input type="email" name="user_email" id="user_email" class="mw-user-email-input input <?php echo isset ( $invalidu ) ? 'invalid' : ''; ?>" value="" size="20" placeholder="<?php echo $forgot_username_password_email_placeholder; ?>" required>
                    </div>

                    <div class="reset-submit">
                        <input class="mw-forgot-username-button" type="submit" name="wp-submit" id="wp-submit" value="<?php echo $u_button; ?>">
                    </div>

                    <?php wp_nonce_field( $nonce_action, $nonce_field ); ?>
                </form>
            </div>

            <?php
            if ( ! empty( $forgot_password_link ) ) {
                ?>
                <div class="tfs-mw-wrapper-block-forgot-username">
                    <a onClick="fp_section_toggle();"><?php echo $forgot_password_link; ?></a>
                </div>
                <?php
            }
            ?>
        </div>
    <?php
    }
    ?>
</div>
