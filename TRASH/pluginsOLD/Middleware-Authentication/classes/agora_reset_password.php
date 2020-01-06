<?php
/**
 * Handles reset password functionality including secure login links, tokenized reset links, username requests,
 * multiple user logins, and sending related emails
 */

/**
 * Class agora_reset_password
 *
 * @author: Threefold Systems
 * @since: 1.9
 */
class agora_reset_password
{
    /**
     * Constructor method
     *
     * @method constructor
     *
     * @param $core mixed Instance of agora_core_framework
     * @param $config array The authentication configuration settings
     */
    public function __construct(agora_core_framework $core, $config)
    {
        $this->core = $core;
        $this->config = $config;
        add_shortcode('agora_middleware_forgot_password', array($this, 'password_reset'));
    }


    /**
     * Handles the base password reset page
     *
     * @method password_reset
     *
     * @return string
     */
    public function password_reset()
    {
        $get_transient = ( isset( $_GET[ 't' ] ) ? sanitize_text_field( $_GET[ 't' ] ) : '' );
        $get_mode = ( isset( $_GET[ 'mode' ] ) ? sanitize_text_field( $_GET[ 'mode' ] ) : '' );

        $this->core->view->set_template_path(dirname(__FILE__) . '/theme');

        $content['nonce_field'] = 'forgot_password_nonce';
        $content['nonce_action'] = 'mw_forgot_password';

        $post_user_email = ( isset( $_POST[ 'user_email' ] ) ? sanitize_text_field( $_POST[ 'user_email' ] ) : '' );
        $post_multi_username = ( isset( $_POST[ 'multi-username' ] ) ? sanitize_text_field( $_POST[ 'multi-username' ] ) : '' );
        $post_multi_data = ( isset( $_POST[ 'multi-data' ] ) ? sanitize_text_field( $_POST[ 'multi-data' ] ) : '' );

        $post_nonce_field = ( isset( $_POST[$content['nonce_field']] ) ? sanitize_text_field($_POST[$content['nonce_field']]) : '' );

        if ($post_user_email AND wp_verify_nonce($post_nonce_field, $content['nonce_action'])) {
            $request_handle_feedback = $this->request_handler($_POST);

            if( empty($reset_page_feedback) ) {
                $content['message'] = $this->core->get_language_variable('txt_forgot_email_sent', array('email' => $post_user_email));
            }
        } elseif ($get_transient AND wp_verify_nonce($post_nonce_field, $content['nonce_action'])) {
            $reset_page_feedback = $this->reset_page($get_transient);

            if( empty($reset_page_feedback)) {
                $content['successful'] = true;
            }
        } elseif (!empty($post_multi_username) && !empty($post_multi_data)) {
            $content['message_class'] = 'error';
            $content['message'] = $this->core->get_language_variable('txt_fb_assign_error');
        }

        if( ! empty( $request_handle_feedback ) ) {
            $content['message'] = $request_handle_feedback['message'];
            $content['message_class'] = $request_handle_feedback['type'];
        }

        if ( ! empty( $reset_page_feedback ) ) {
            $content['message'] = $reset_page_feedback;
        }

        if ( ! empty( $get_mode ) && $get_mode == 'temp' ) {
            $content['temp_message'] = $this->core->get_language_variable('txt_temporary_password');
        }

        if (!empty($this->config['use_new_password_reset'])) {
            $content['use_new_password_reset'] = $this->config['use_new_password_reset'];
        }

        if (!empty($this->config['magic_link'])) {
            $content['magic_link'] = $this->config['magic_link'];
            $content['magic_link_button_label'] = $this->core->get_language_variable('txt_magic_link_button_label');
            $content['subtitle'] = $this->core->get_language_variable('txt_default_reset_password_magic_link_message');
        } else {
            $content['subtitle'] = $this->core->get_language_variable('txt_default_reset_password_message');
        }

        $content['login_title'] = $this->core->get_language_variable('txt_login_title');
        $content['pw_title'] = $this->core->get_language_variable('txt_reset_pwd_title');
        $content['u_title'] = $this->core->get_language_variable('txt_reset_username_title');
        $content['pw_subtitle'] = $this->core->get_language_variable('txt_reset_pwd_subtitle');
        $content['u_subtitle'] = $this->core->get_language_variable('txt_reset_username_subtitle');
        $content['password_reset_subtitle'] = $this->core->get_language_variable('txt_reset_password_subtitle');
        $content['invalid'] = $this->core->get_language_variable('txt_invalid_link');
        $content['pw_toggle'] = $this->core->get_language_variable('txt_pwd_toggle');
        $content['u_toggle'] = $this->core->get_language_variable('txt_username_toggle');
        $content['email_label'] = $this->core->get_language_variable('txt_email_input_label');
        $content['new_pw_label'] = $this->core->get_language_variable('txt_new_pwd_input_label');
        $content['confirm_pw_label'] = $this->core->get_language_variable('txt_confirm_pwd_input_label');
        $content['u_label'] = $this->core->get_language_variable('txt_username_input_label');
        $content['change_pw_title'] = $this->core->get_language_variable('txt_change_pwd_title');
        $content['secure_label'] = $this->core->get_language_variable('txt_secure_link_option_label');
        $content['reset_label'] = $this->core->get_language_variable('txt_reset_option_label');
        $content['forgot_username_link'] = $this->core->get_language_variable('txt_forgot_username_link');
        $content['forgot_password_link'] = $this->core->get_language_variable('txt_forgot_password_link');
        $content['new_pwd_username_input_label'] = $this->core->get_language_variable('txt_new_pwd_username_input_label');
        $content['reset_password_username_placeholder'] = $this->core->get_language_variable('txt_reset_password_username_placeholder');
        $content['reset_password_new_password_placeholder'] = $this->core->get_language_variable('txt_reset_password_new_password_placeholder');
        $content['reset_password_confirm_password_placeholder'] = $this->core->get_language_variable('txt_reset_password_confirm_password_placeholder');
        $content['forgot_username_password_email_placeholder'] = $this->core->get_language_variable('txt_forgot_username_password_email_placeholder');

        $content['password_reset_successful'] = $this->core->get_language_variable('txt_password_reset_successful');

        $content['home_link'] = $this->core->get_language_variable('txt_home_link');

        if ( empty( $content['home_link'] ) ) {
            $content['home_link'] = 'Return to home';
        }

        $content['u_button'] = $this->core->get_language_variable('txt_username_button');

        if ( empty( $content['u_button'] ) ) {
            $content['u_button'] = 'SEND REMINDER';
        }

        $content['change_pw_button'] = $this->core->get_language_variable('txt_change_pwd_button');

        if ( empty( $content['change_pw_button'] ) ) {
            $content['change_pw_button'] = 'RESET PASSWORD';
        }

        $content['pw_button'] = $this->core->get_language_variable('txt_pwd_button');

        if ( empty( $content['pw_button'] ) ) {
            $content['pw_button'] = 'SEND RESET LINK';
        }

        if ( ! empty( $_GET[ 'teue' ] ) ) {
            $email_address = $this->decrypt_password_reset_email_address( esc_attr( $_GET[ 'teue' ] ) );
        } else {
            $email_address = '';
        }

        if ( ! empty( $_GET[ 'username' ] ) ) {
            $username = $this->decrypt_password_reset_email_address( esc_attr( $_GET[ 'username' ] ) );
        } else {
            $username = '';
        }

        $content['get_username'] = $username;
        $content['get_email'] = $email_address;

        return $this->core->view->load('mw-forgot-username-password', $content, true);
    }


    /**
     * Determines which email the customer is requesting and directs to the relevant function to generate
     *
     * @method request_handler
     *
     * @param $form_vars array Users inputs from frontend
     * @return array Error/success message
     */
    private function request_handler( $form_vars )
    {
        if ( filter_var( $form_vars[ 'user_email' ], FILTER_VALIDATE_EMAIL ) ) {
            // Get the users password(s) and email it to them.
            $email_address = addslashes($form_vars['user_email']);

            $login = $this->core->user->get_login_by_email( $email_address );

            $login = apply_filters('agora_mw_lost_password', $login);

            $post_user_email = ( isset( $_POST[ 'user_email' ] ) ? sanitize_text_field( $_POST[ 'user_email' ] ) : '' );

            if ( ! is_wp_error( $login ) ) {
                $response = '';

                if ( isset ( $form_vars[ 'mode' ] ) AND $form_vars[ 'mode' ] == 'p' ) {
                    $response = $this->generate_password_email( $email_address, $login );
                } else if ( isset( $form_vars['mode' ] ) AND $form_vars[ 'mode' ] == 'ml' ) {
                    $response = $this->generate_magic_link_email( $email_address, $login );
                } else if ( isset( $form_vars[ 'mode' ] ) AND $form_vars[ 'mode' ] == 'u' ) {
                    $response = $this->generate_username_email( $email_address, $login );
                }

                if ( $response ) {
                    // Timeout?
                    if ( is_array( $response ) && array_key_exists( 'time' , $response )  ) {
                        return array(
                            'message' => $this->core->get_language_variable(
                                'txt_changed_password_recently',
                                array(
                                    'time' => $response[ 'time' ]
                                )
                            ),
                            'type' => 'error',
                        );
                    }

                    $message = $this->core->get_language_variable( 'txt_forgot_email_sent', array( 'email' => $email_address ) );

                    if ( isset ( $form_vars[ 'mode' ] ) AND $form_vars[ 'mode' ] == 'p' ) {
                        $message = $this->core->get_language_variable( 'txt_email_sent_reset_password' );
                    } else if ( isset( $form_vars['mode' ] ) AND $form_vars[ 'mode' ] == 'ml' ) {
                        $message = $this->core->get_language_variable( 'txt_email_sent_magic_link' );
                    } else if ( isset( $form_vars[ 'mode' ] ) AND $form_vars[ 'mode' ] == 'u' ) {
                        $message = $this->core->get_language_variable( 'txt_email_sent_forgot_username' );
                    }

                    return array(
                        'message' => $message,
                        'type' => 'success',
                    );
                }
            } elseif (agora_middleware_user::retrieve_password($post_user_email) === true) {
              return array(
                  'message' => $this->core->get_language_variable( 'txt_forgot_email_sent', array( 'email' => $email_address ) ),
                  'type' => 'success',
              );
            } else {
                $this->core->log->error(__('Someone made a bad lost password request'));

                return array(
                    'message' => $this->core->get_language_variable('txt_account_not_found'),
                    'type' => 'error',
                );
            }
        }

        return array(
            'message' => $this->core->get_language_variable('txt_invalid_email_address'),
            'type' => 'error',
        );
    }


    /**
     * Generates the email for reset password and request password
     *
     * @method generate_password_email
     *
     * @param $email_address string Email address the mail will be sent to
     * @param $login mixed User details
     *
     * @return string
     */
    private function generate_password_email( $email_address, $login )
    {
        // Check transient to see if password reset email has been sent out in the last 15 minutes
        $transNameTime = $this->core->tfs_hash( 'tfs_ct' . $email_address );

        $pw_reset_delay = 15;
        $timeTrans = get_transient($transNameTime);

        if ( ! empty( $timeTrans ) ) {
            $current_time = round(abs(strtotime(current_time('H:i:s')) - strtotime($timeTrans)) / 60);
            $remaining_time_in_minutes = $pw_reset_delay - $current_time;
            $remaining_time_in_minutes = $remaining_time_in_minutes == 0 ? '1' : $remaining_time_in_minutes;
            $minute = $remaining_time_in_minutes == 1 ? 'minute' : 'minutes';
            $remaining_time = $remaining_time_in_minutes . ' ' . $minute;

            return array(
                'time' => $remaining_time
            );
        } else {
            $snippet = '';
            $email_text = '';
            $type = 'forgot_password_plaintext';

            // If {{accounts}} exists in language variable
            if ( strpos( $this->core->get_language_variable( 'inp_forgot_password_email_text' ), '{{accounts}}' ) ) {
                $accounts = $this->core->mw->get_account_by_email( $email_address );

                if ( $this->config[ 'use_new_password_reset' ] ) {
                    $type = 'forgot_password';
                }

                $accounts = $this->setup_account_info_for_email( $email_address, $accounts, $type );

                if ( ! empty( $accounts ) && is_array( $accounts ) ) {
                    $snippet = $this->build_email_with_accounts( $accounts, $type );

                    if ( $snippet ) {
                        if ( $this->config[ 'use_new_password_reset' ] ) {
                            $email_text = $this->core->get_language_variable( 'inp_forgot_password_email_text' , array( 'accounts' => $snippet ) );
                        } else {
                            $email_text = $this->core->get_language_variable( 'inp_password_reminder_email_text' , array( 'accounts' => $snippet ) );
                        }
                    }
                }
            }

            // If {{link}} exists in language variable or there wer eno accounts for the 'account' email
            if ( strpos( $this->core->get_language_variable( 'inp_forgot_password_email_text' ), '{{link}}' ) || ! $snippet ) {
                if ( $this->config[ 'use_new_password_reset' ] ) {
                    $type = 'forgot_password';

                    $snippet = $this->tokenized_reset_link( $email_address, $login['0']->username );
                } else {
                    $snippet = $this->render_lost_password( $login );
                }

                $email_text = $this->core->get_language_variable( 'inp_forgot_password_email_text' , array( 'link' => $snippet ) );
            }

            $response = $this->send_email( $email_text, $email_address, $type );

            if ( $response ) {
                return $response;
            }
        }

        return false;
    }


    /**
     * Generates the tokenized reset link
     *
     * @method tokenized_reset_link
     *
     * @param $email_address string Email address used in the link
     * @param $login string Login info
     *
     * @return string Tokenized reset link
     */
    private function tokenized_reset_link( $email_address, $login )
    {
        $transName = $this->core->tfs_hash( 'PR_' . $email_address );
        set_transient( $transName, $login, HOUR_IN_SECONDS );

        if ( ! empty( $_SERVER[ 'HTTP_REFERER' ] ) ) {
            $snippet = explode( '?', $_SERVER[ 'HTTP_REFERER' ] );
            $snippet = $snippet[0];
        } else {
            $snippet = $_SERVER[ 'REQUEST_SCHEME' ] . '://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REDIRECT_URL' ];
        }

        return $snippet . '?t=' . $transName . '&teue=' . $this->encrypt_password_reset_email_address( $email_address ) . '&username=' . $this->encrypt_password_reset_email_address( $login );
    }


    /**
     * Generates the secure login link
     *
     * @method generate_magic_link
     *
     * @param $email_address string Email address the mail will be sent to
     * @param $login array User details
     *
     * @return $response|bool
     */
    private function generate_magic_link_email( $email_address, $login )
    {
        $email_text = '';
        $snippet = '';
        $type = 'magic_link';

        $accounts = $this->core->mw->get_account_by_email( $email_address );

        // If {{accounts}} exists in language variable
        if ( strpos( $this->core->get_language_variable( 'inp_magic_link_text' ), '{{accounts}}' ) ) {
            $accounts = $this->setup_account_info_for_email( $email_address, $accounts, $type );

            if ( ! empty( $accounts ) && is_array( $accounts ) ) {
                $snippet = $this->build_email_with_accounts( $accounts, $type );

                if ( $snippet ) {
                    $email_text = $this->core->get_language_variable( 'inp_magic_link_text' , array( 'accounts' => $snippet ) );
                }
            }
        }

        // {{link}}
        if ( strpos( $this->core->get_language_variable( 'inp_magic_link_text' ), '{{link}}' ) || ! $snippet ) {
            $snippet = explode('?', $_SERVER['HTTP_REFERER']);

            $snippet = $snippet[0];

            $accounts = apply_filters( 'validate_user_accounts', $accounts );

            $magicLinkTransient = $this->core->tfs_hash( 'magic_link' . $email_address );

            set_transient( $magicLinkTransient, $accounts, HOUR_IN_SECONDS );

            if ( sizeof( $accounts ) == 1 ) {
                $snippet .= '?ml=' . $magicLinkTransient . '&teue=' . $this->encrypt_password_reset_email_address( $email_address );
            } else {
                $snippet .= '?multiple-users=' . $magicLinkTransient . '&sl=true&teue=' . $this->encrypt_password_reset_email_address($email_address);
            }

            if ( $snippet ) {
                $email_text = $this->core->get_language_variable( 'inp_magic_link_text' , array( 'link' => $snippet ) );
            }
        }

        $response = $this->send_email( $email_text, $email_address, $type );

        if ( $response ) {
            return $response;
        }

        return false;
    }


    /**
     * Generates the username request email
     *
     * @method generate_username_email
     *
     * @param $email_address string Email address the mail will be sent to
     * @param $login array User details
     *
     * @return $response|bool
     */
    private function generate_username_email( $email_address, $login )
    {
        $email_text = '';
        $type = 'forgot_username';
        $snippet = '';

        // If {{accounts}} exists in language variable
        if ( strpos( $this->core->get_language_variable( 'inp_forgot_username_text' ), '{{accounts}}' ) ) {
            $accounts = $this->core->mw->get_account_by_email( $email_address );

            $accounts = $this->setup_account_info_for_email( $email_address, $accounts, $type );

            if ( ! empty( $accounts ) && is_array( $accounts ) ) {
                $snippet = $this->build_email_with_accounts( $accounts, $type );

                if ( $snippet ) {
                    $email_text = $this->core->get_language_variable( 'inp_forgot_username_text' , array( 'accounts' => $snippet ) );
                }
            }
        }

        if ( strpos( $this->core->get_language_variable( 'inp_forgot_username_text' ), '{{login}}' ) || ! $snippet ) {
            // {{login}}
            $i = 0;

            if ( is_array( $login ) ) {
                foreach ( $login as $l ) {
                    if ( $i !== 0 ) {
                        $snippet .= ', ';
                    }

                    $snippet .= $l->username;
                    $i++;
                }
            }

            if ( $snippet ) {
                $email_text = $this->core->get_language_variable( 'inp_forgot_username_text' , array( 'login' => $snippet ) );
            }
        }

        $response = $this->send_email( $email_text, $email_address, $type );

        if ( $response ) {
            return $response;
        }

        return false;
    }


    /**
     * Handles sending the email to the customer
     *
     * @method send_email
     *
     * @param $snippet string Content for the email
     * @param $email_address string Email address the mail will be sent to
     * @param $type array Which email is being sent
     * @param $account_details array Accounts
     *
     * @return bool
     */
    public function send_email( $snippet, $email_address, $type )
    {
        add_filter( 'wp_mail_content_type', array( $this, 'mw_authentication_set_html_content_type' ) );

        if ( $type == 'forgot_password' ) {
            $subject = 'txt_forgot_password_email_subject';
        } else if ( $type == 'forgot_password_plaintext' ) {
            $subject = 'txt_forgot_password_plaintext_email_subject';
        } else if ( $type == 'forgot_username' ) {
            $subject = 'txt_forgot_username_email_subject';
        } else if ( $type == 'magic_link' ) {
            $subject = 'txt_magic_link_email_subject';
        } else if ( $type == 'after_password_reset' ) {
            $subject = 'txt_after_password_reset_email_subject';
            $snippet = $this->core->get_language_variable( 'inp_after_password_reset_email_text', array( 'link' => $snippet ) );
        } else {
            // triggered
            $subject = 'txt_forgot_password_username_email_subject';
            $snippet = $this->core->get_language_variable( 'inp_failed_login_email_text', array( 'link' => $snippet ) );
        }

        // Add title
        $title = $this->core->get_language_variable( $subject );

        $email_content = '';

        if ( $title ) {
            $email_content .= '<h1>' . $title . '</h1>';
            $email_content .= $this->add_nl();
        }

        $email_content .= $snippet;

        if ( $this->config[ 'html_email' ] == 1 ) {
            $email_content = nl2br( $email_content );

            $email_content = $this->core->view->load( 'mw-email-template', $email_content, true );
        } else {
            $email_content = wp_strip_all_tags( $email_content, false );
        }

        $headers = 'From: ' . $this->core->get_language_variable( 'txt_forgot_password_email_from' );

        $this->core->log->notice( 'New FP Email Sent to ' . $email_address );

        $mail_config = get_option('agora_core_framework_config_mc');

        if ( isset( $mail_config[ 'mc_mailing' ] ) AND $mail_config[ 'mc_mailing'] == 1 ) {
            $this->core->mc->put_trigger_mailing(
                $mail_config['mc_mailing_id'],
                $email_address,
                array(
                    'email_body' => $email_content
                )
            );
        } else {
            wp_mail(
                $email_address,
                $this->core->get_language_variable( $subject ),
                $email_content,
                $headers
            );
        }

        if ( $type == 'forgot_password' ) {
            $transNameTime = $this->core->tfs_hash( 'tfs_ct' . $email_address );
            set_transient( $transNameTime, current_time( 'H:i:s' ), 15 * MINUTE_IN_SECONDS );
        }

        remove_filter( 'wp_mail_content_type', array( $this, 'mw_authentication_set_html_content_type' ) );

        return true;
    }


    /**
     * Build 'accounts' section for emails
     *
     * @method build_email_with_accounts
     *
     * @param $accounts array List of accounts
     * @param $type string Type of password reset
     *
     * @return string
     */
    private function build_email_with_accounts( $accounts, $type )
    {
        $accounts_block = '';
        $account_subtitle = '';

        if ( $type == 'forgot_password' ) {
            if ( count( $accounts ) > 1 ) {
                $account_subtitle = $this->core->get_language_variable( 'txt_forgot_password_email_multiple_accounts' );
            }
        } else if ( $type == 'forgot_password_plaintext' ) {
            if ( count( $accounts ) > 1 ) {
                $account_subtitle = $this->core->get_language_variable( 'txt_forgot_password_plaintext_multiple_accounts' );
            }
        } else if ( $type == 'forgot_username' ) {
            if ( count( $accounts ) > 1 ) {
                $account_subtitle = $this->core->get_language_variable( 'txt_forgot_username_multiple_accounts' );
            }
        } else if ( $type == 'magic_link' ) {
            if ( count( $accounts ) > 1 ) {
                $account_subtitle = $this->core->get_language_variable( 'txt_magic_link_multiple_accounts' );
            }
        }

        // Add subtitle if there's more than 1 account
        if ( $account_subtitle ) {
            $accounts_block .= $account_subtitle . '<br /><br />';
            $accounts_block .= $this->add_nl();
        }

        // Get the last item in the accounts array
        $array_keys = array_keys( $accounts );
        $last_account_key = array_pop( $array_keys );

        $account_counter = 0;

        foreach ( $accounts as $account_key => $account ) {
            $account_counter++;

            if ( $account_counter == 1 ) {
                $accounts_block .= $this->add_nl();
            } else {
                $accounts_block .= '<br /><br /><hr /><br />';
                $accounts_block .= $this->add_nl();
            }

            if ( $account[ 'label_username' ] ) {
                $accounts_block .= '<p><strong>' . $account[ 'label_username' ] . '</strong></p>';

                $accounts_block .= $this->add_nl();
            }

            if ( ! empty( $account['subscriptions'] ) && is_array( $account['subscriptions'] ) ) {
                if ( $account[ 'label_subscriptions' ] ) {
                    $accounts_block .= $account[ 'label_subscriptions' ];
                    $accounts_block .= $this->add_nl();
                }

                $count_subscriptions = 1;

                $accounts_block .= '<ul>';

                foreach ( $account['subscriptions'] as $subscription ) {
                    // If on the 5th subscriptions, say there's x more
                    if ( $count_subscriptions == 5 ) {
                        $remaining_subscriptions = count( $account['subscriptions'] ) - 4;

                        $text_remaining_subscriptions = $this->core->get_language_variable( 'txt_plus_x_more_subscriptions' , array( 'number' => $remaining_subscriptions ) );

                        $accounts_block .= '<li>' . $text_remaining_subscriptions . '</li>';
                        break;
                    }

                    $accounts_block .= '<li>' . $subscription . '</li>';
                    $accounts_block .= $this->add_nl();

                    $count_subscriptions++;
                }

                $accounts_block .= '</ul>';
            }

            $link = $account[ 'link' ];
            $label_link = $account[ 'label_link' ];

            if ( $type == 'forgot_password_plaintext' ) {
                $accounts_block .= '<strong>' . $this->core->get_language_variable( 'txt_forgot_password_is' ) . '</strong> ' . $account['password'];
            } else {
                $accounts_block .= '<strong>' . $label_link . ':</strong> <a href="' . $link .  '">' . $link . '</a>';
                $accounts_block .= $this->add_nl();
            }

            if ( count( $accounts ) > 1 && $last_account_key != $account_key ) {
                $accounts_block .= $this->add_nl();
            } else {
                $accounts_block .= '<br />';
            }
        }

        if ( $type == 'magic_link' ) {
            $accounts_block .= $this->add_nl();
            $accounts_block .= '<br />' . $this->core->get_language_variable( 'txt_magic_link_link_expiration' );
        }

        return $accounts_block;
    }


    /**
     * Add New line for plain text emails
     *
     * @method add_nl
     *
     * @param $accounts_block string Content to add NL to
     *
     * @return string Content
     */
    private function add_nl()
    {
        if ( ! $this->config[ 'html_email' ] ) {
            return "\r\n";
        }
    }


    /**
     * Setup account info for email
     *
     * @method setup_account_info_for_email
     *
     * @param $email_address string Email address the mail will be sent to
     * @param $accounts mixed User accounts
     * @param $email_type string Type of email sent
     *
     * @return
     */
    private function setup_account_info_for_email( $email_address, $accounts, $email_type )
    {
        $account_details = array();
        $email_multiple_accounts_same_username = array();

        if ( $accounts && is_array( $accounts ) ) {
            foreach ( $accounts as $account ) {
                // If username reminder or magic link or password reset
                // and there's more than 1 account with the same username - don't include same usernames
                if ( $email_type == 'forgot_username' || $email_type == 'magic_link' || $email_type == 'forgot_password' ) {
                    if ( in_array( $account->id->userName, $email_multiple_accounts_same_username ) ) {
                        continue;
                    } else {
                        array_push( $email_multiple_accounts_same_username, $account->id->userName );
                    }
                }

                $return_subscriptions = array();

                // Get subscriptions by account details
                $subscriptions = $this->core->mw->get_subscriptions_by_login( $account->id->userName, $account->password );

                if ( $subscriptions && is_array( $subscriptions ) ) {
                    foreach ( $subscriptions as $subscription ) {
                        $return_subscriptions[] = $subscription->id->item->itemDescription;
                    }
                }

                $return_account = array(
                    'label_username' => $this->core->get_language_variable('txt_forgot_password_email_username_label') . ' ' . $account->id->userName,
                    'password' => $account->password,
                );

                if ( $email_type == 'forgot_password' ) {
                    $return_account[ 'link' ] = $this->tokenized_reset_link( $email_address, $account->id->userName );
                    $return_account[ 'label_link' ] = $this->core->get_language_variable('txt_reset_password_email_link_label') . ' ' . $account->id->userName;
                } else if ( $email_type == 'forgot_username' ) {
                    $login_url = add_query_arg(
                        array(
                            'username' => $this->encrypt_password_reset_email_address( $account->id->userName )
                        ),
                        get_permalink( get_page_by_path( 'login' ) )
                    );

                    $return_account[ 'link' ] = $login_url; // link to login page and populate username
                    $return_account[ 'label_link' ] = $this->core->get_language_variable('txt_forgot_username_email_link_label') . ' ' . $account->id->userName;
                } else if ( $email_type == 'magic_link' ) {
                    $snippet = explode( '?', $_SERVER[ 'HTTP_REFERER' ] );
                    $snippet = $snippet[ 0 ];
                    $magic_link_transient = $this->core->tfs_hash( 'magic_link' . $email_address . $account->id->userName );

                    set_transient( $magic_link_transient, $account, HOUR_IN_SECONDS );

                    $snippet .= '?ml=' . $magic_link_transient . '&teue=' . $this->encrypt_password_reset_email_address( $email_address );

                    $return_account[ 'link' ] = $snippet; // link to login page and populate username
                    $return_account[ 'label_link' ] = $this->core->get_language_variable('txt_magic_link_email_link_label') . ' ' . $account->id->userName;
                }

                if ( $return_subscriptions ) {
                    $return_account[ 'label_subscriptions' ] = $this->core->get_language_variable('txt_password_email_subscriptions_label');
                    $return_account[ 'subscriptions' ] = $return_subscriptions;
                }

                array_push( $account_details, $return_account );
            }
        }

        if ( $account_details ) {
            return $account_details;
        }

        return false;
    }


    /**
     * Reads the url for secure login vars and sets the auto login session var
     *
     * @method magic_link
     */
    public function magic_link()
    {
        $get_ml = ( isset( $_GET[ 'ml' ] ) ? sanitize_text_field( $_GET[ 'ml' ] ) : '' );
        $get_email = ( isset( $_GET[ 'teue' ] ) ? $this->decrypt_password_reset_email_address( sanitize_text_field( $_GET[ 'teue' ] ) ) : '' );

        if ( ! empty( $get_ml ) && ! empty( $get_email ) ) {
            $user = get_transient( $get_ml );

            if ( $user !== false ) {
                $_SESSION["secure_login_reset_link"] = $this->tokenized_reset_link( $get_email, $user->id->userName );

                // render multi users box here
                $_SESSION["auto_login"] = $user;

                delete_transient($get_ml);

                session_write_close();

                wp_redirect(home_url());
                exit;
            } else {
                $_SESSION["auto_login_fail"] = true;

                session_write_close();

                wp_redirect(home_url());

                exit;
            }
        }
    }


    /**
     * Reads the url for multiple user login vars and sets the auto login session var
     *
     * @method multiple_users
     */
    public function multiple_users()
    {
        $get_email = ( isset( $_GET[ 'teue' ] ) ? $this->decrypt_password_reset_email_address( sanitize_text_field( $_GET[ 'teue' ] ) ) : '' );

        $post_multi_username = ( isset( $_POST[ 'multi-username' ] ) ? sanitize_text_field( $_POST[ 'multi-username' ] ) : '' );
        $post_multi_data = ( isset( $_POST[ 'multi-data' ] ) ? sanitize_text_field( $_POST[ 'multi-data' ] ) : '' );

        if (!empty($post_multi_username) && !empty($post_multi_data) && !empty($get_email)) {
            $accounts = get_transient($post_multi_data);
            if (!empty($accounts)) {
                $post_secure_link = ( isset( $_POST[ 'secure_link' ] ) ? sanitize_text_field( $_POST[ 'secure_link' ] ) : '' );

                foreach ($accounts as $account) {
                    if (strtoupper($account->id->userName) == strtoupper($post_multi_username)) {
                        if(!empty($post_secure_link)) {
                            $_SESSION["secure_login_reset_link"] = $this->tokenized_reset_link( $get_email, $account->id->userName );
                        }
                        $_SESSION["auto_login"] = $account;
                        session_write_close();
                        wp_redirect(home_url());
                        exit;
                    }
                }
            }
            $_SESSION["auto_login_fail"] = true;
            session_write_close();
            wp_redirect(home_url());
            exit;
        }
    }


    /**
     * Renders the tokenized reset page and processes the password reset
     *
     * @method reset_page
     *
     * @param $transName string The name of the rest password transient for the user
     *
     * @return string Feedback message
     */
    private function reset_page($transName)
    {
        $get_email = ( isset( $_GET[ 'teue' ] ) ? $this->decrypt_password_reset_email_address( sanitize_text_field( $_GET[ 'teue' ] ) ) : '' );
        $get_transient = ( isset( $_GET[ 't' ] ) ? sanitize_text_field( $_GET[ 't' ] ) : '' );

        $linkValid = get_transient($transName);

        $post_new_password = ( isset( $_POST[ 'new-password' ] ) ? sanitize_text_field( $_POST[ 'new-password' ] ) : '' );
        $post_confirm_password = ( isset( $_POST[ 'confirm-password' ] ) ? sanitize_text_field( $_POST[ 'confirm-password' ] ) : '' );
        $post_username = ( isset( $_POST[ 'username' ] ) ? sanitize_text_field( $_POST[ 'username' ] ) : '' );

        if ($linkValid !== false) {
            if ($post_new_password !== '' AND $post_new_password !== NULL) {
                //prevent users setting a new password with the temporary password prefix
                if (strtolower(substr(trim($post_new_password), 0, 6)) == "nd312_") {
                    $content['status_message'] = $this->core->get_language_variable('txt_password_reset_invalid_combination');
                } else if ($post_new_password === $post_confirm_password) {
                    $user = $this->core->mw->get_account_by_email($get_email);
                    $valid_user = false;

                    foreach ($user as $account) {
                        if (strtoupper($account->id->userName) == strtoupper($post_username)) {
                            $valid_user = true;
                            $password_hashing = false;

                            // Password hashing
                            if ( $this->config[ 'password_hashing' ] ) {
                                if ( $this->config[ 'password_hashing' ] == 1 ) {
                                    $password_hashing = true;

                                    $this->core->mw->put_password_reset( $account->id->userName, $post_new_password );
                                }
                            }

                            if ( $password_hashing === false ) {
                                $this->core->mw->put_update_password($account->customerNumber, $account->id->userName, $account->password, $post_new_password);
                            }


                            // Set transients after having reset the password successfully
                            // Set transient for username and email address as not sure which one may be used to login
                            $trans_name_email = $this->core->tfs_hash( 'tfs_prs_email_' . strtolower( $get_email ) );
                            set_transient( $trans_name_email, 1, 15 * MINUTE_IN_SECONDS );

                            $trans_name_username = $this->core->tfs_hash( 'tfs_prs_username_' . strtolower( $post_username) );
                            set_transient( $trans_name_username, 1, 15 * MINUTE_IN_SECONDS );


                            $login_link = home_url(). '/login/?username=' . $this->encrypt_password_reset_email_address( $account->id->userName );

                            $this->send_email( $login_link, $get_email, 'after_password_reset' );


                            $content['status_message'] = $this->core->get_language_variable('txt_password_reset_successful');
                            $content['successful'] = true;

                            $account_to_log_in = new stdClass();

                            $account_to_log_in->username = $account->id->userName;
                            $account_to_log_in->password = $account->password;
                            delete_transient($get_transient);

                            $_SESSION["auto_login"] = $account;

                            if(defined('REDIRECT_PASSWORD')){
                                echo "<div class='alert alert-success'>Password Has been changed <a href='". home_url() . REDIRECT_PASSWORD. "'>Click here</a></div>";
                            }
                            break;
                        }
                    }

                    if (!$valid_user) {
                        $message = $this->core->get_language_variable('txt_password_reset_invalid_user');

                        return $message;
                    }
                } else {
                    return $this->core->get_language_variable('txt_password_reset_no_match');
                }
            }
        } else {
           return $this->core->get_language_variable('txt_password_reset_invalid_link');
        }
    }


    /**
     * Function to render the lost password snippet that gets inserted into an email
     *
     * @method render_lost_password
     *
     * @param $logins User details
     *
     * @return string Lost password or list for passwords for password request email
     */
    private function render_lost_password( $logins )
    {
        $snippet = '';

        if ( $this->config['html_email'] == 1 ) {
            $separator = "<br>";
        } else {
            $separator = "\n";
        }

        if ( is_array( $logins ) ) {
            $array_keys = array_keys( $logins );
            $last_login_key = array_pop( $array_keys );

            foreach ( $logins as $login_key => $l ) {
                if (isset($l->username)) {
                    $snippet .= __('Username: ') . $l->username . $separator . __('Password: ') . $l->password;
                } else {
                    $snippet .= __('Password: ') . $l->password;
                }

                if ( $last_login_key != $login_key ) {
                    $snippet .= $separator . $separator;
                }
            }

            /**
             * If there's more than one we need to include a note about multiple logins
             */
            if (sizeof($logins) > 1) {
                $snippet = $this->core->get_language_variable('txt_multiple_logins_found') . $separator . $separator . $snippet;
            }
        }

        return $snippet;
    }


    /**
     * Shortcode function to render the multiple user login template
     *
     * @method multiple_users_shortcode
     */
    public function multiple_users_shortcode()
    {
        $get_multiple_users = ( isset( $_GET[ 'multiple-users' ] ) ? sanitize_text_field( $_GET[ 'multiple-users' ] ) : '' );

        if ( ! empty( $get_multiple_users ) ) {
            $accounts = get_transient( $get_multiple_users );
            if ( ! empty( $accounts ) ) {
                $content = array(
                    'message' => $this->core->get_language_variable('txt_multiple_users'),
                    'accounts' => $accounts,
                    'label_username' => $this->core->get_language_variable('txt_login_username_label'),
                    'label_log_in' => $this->core->get_language_variable('txt_login_button'),
                );

                $this->core->view->load('multiple_user_shortcode', $content);
            }
        }
    }


    /**
     * Sets the email content type
     *
     * @method mw_authentication_set_html_content_type
     *
     * @return string Email content type
     */
    public function mw_authentication_set_html_content_type()
    {
        if ( $this->config[ 'html_email' ] == 1 ) {
            return 'text/html';
        } else {
            return 'text/plain';
        }
    }


    /**
     * Encrypt password reset email address
     *
     * @method encrypt_password_reset_email_address
     *
     * @return string Email $email_address
     */
    public function encrypt_password_reset_email_address( $email_address )
    {
        return base64_encode( $email_address );
    }


    /**
     * Decrypt password reset email address
     *
     * @method decrypt_password_reset_email_address
     *
     * @return string Email $email_address
     */
    public function decrypt_password_reset_email_address( $email_address )
    {
        return base64_decode( $email_address );
    }
}