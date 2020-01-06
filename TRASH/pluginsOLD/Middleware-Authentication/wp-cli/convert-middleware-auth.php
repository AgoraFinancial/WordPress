<?php
/**
 * Description: script to update wp_user data from old MiddleWare plugin customer to new pubsvs plugin
 *
 * Only runs via WP-CLI. Intended to be dumped into a file. IE:
 *
 * $ wp --url=http://[siteurl] auth convert > auth_conversion_results.txt
 *
 */
WP_CLI::add_command( 'mw-auth', 'Convert_Middleware_Auth' );


class Convert_Middleware_Auth extends WP_CLI_Command {

	/**
	 * Do the actual conversions.
     */
	function convert() {

		add_action( 'pre_user_query', array( $this, 're_parse_query') );

		global $wpdb;

		// We only want subscribers with "@agora-middleware.com" in their email addresses. This gives us only
		// unconverted users, allows restarting without any other logic, and  prevents overwriting data.
		$users = new WP_User_Query ( 
			array( 
				'orderby' => 'ID',
				//'number' => 15, // Can limit number of users processed here
				'search' => '*@agora-middleware.com',
			 	'search_columns' => array(
					'user_email',
				),
				'meta_query' => '',
			)
		);

		$core = agora_core_framework::get_instance();
		$errors = array();

		$i = 0;

		foreach ( $users->get_results() as $user ) {

			echo "--------------------------------------------------\n";

			echo "USER ID: ";
			print_r($user->ID);

			echo " -- LOGIN: ";
			print_r($user->user_login);

			echo "\n";

			if ( (int) $user->user_login == 0 ) {
				echo '*** User ID not numeric, probably not old MW plugin user.';
				continue;
			}

			// Original Middleware
			if ( $user->user_login != $user->user_nicename ) {
				
				if ( strpos($user->user_email, '@agora-middleware.com') !== false ) {
					$query = $wpdb->prepare( 
						"UPDATE wp_users SET user_email = %s WHERE user_login = %s", 
						str_replace('@agora-middleware.com', '@agora-middleware-PROCESSED.com', $user->user_email),
						$user->user_login 
					);
					$wpdb->query( $query );
				}

				echo 'Login and nicename missmatch for user ID: ' . $user->ID . "\n";
				continue;
			}

			// Verify that the email address needs conversion. Anthing that's not '@agora-middleware.com' should be skipped.
			// (And yes, the user query shouldn't even pull them is. This is insurance.)
			if ($user->user_email != $user->user_login . '@agora-middleware.com') {
				echo "Email was did not contain '@agora-middleware.com' for user ID: " . $user->ID . "\n";
				continue;
			}

			$update_user_name = $core->mw->get_customer_by_id( $user->data->user_login );
			$update_customer_name = $core->mw->get_customer_address_by_id( $user->data->user_login );

			// We got valid results form MW2, so process
			if ( ! is_wp_error( $update_customer_name ) &&  ! is_wp_error( $update_user_name ) ) {

				// WordPress likes lowercase user names, so we will force that.
				$new_user_login = strtolower($update_user_name[0]->id->userName);

				// Make sure username is valid.
				if ( ! validate_username( $new_user_login ) ) {
					if ( strpos($user->user_email, '@agora-middleware.com') !== false ) {
						$query = $wpdb->prepare( 
							"UPDATE wp_users SET user_email = %s WHERE user_login = %s", 
							str_replace('@agora-middleware.com', '@agora-middleware-INVALID_USER_NAME.com', $user->user_email),
							$user->user_login 
						);
						//$wpdb->query( $query );
					}

					echo 'New username is invalid: ' . $user->ID . " -- NEW USER LOGIN: $new_user_login\n";
					continue;
				}

				// Make sure username isn't a duplicate.
				if ( username_exists( $new_user_login ) ) {
					if ( strpos($user->user_email, '@agora-middleware.com') !== false ) {
						$query = $wpdb->prepare( 
							"UPDATE wp_users SET user_email = %s WHERE user_login = %s", 
							str_replace('@agora-middleware.com', '@agora-middleware-DUPE_USER_NAME.com', $user->user_email),
							$user->user_login 
						);
						$wpdb->query( $query );
					}

					echo 'New username is a duplicate: ' . $user->ID . "NEW USER LOGIN: $new_user_login\n";
					continue;
				}

				$customer_number = $update_customer_name[0]->id->customerNumber;

				// Do not overwrite display name if set differently than login (MW ID)
				$new_display_name = $user->display_name;
				if ($user->display_name == $user->user_login ) {
					$new_display_name = trim( $update_customer_name[0]->firstName ) . " " . $update_customer_name[0]->lastName[0] . '.';
				}

				// user_nicename needs to be sanitized first, since it's used in 
				$new_user_nicename = sanitize_title( $new_user_login );

				// Get the email.
				$new_user_email = $update_customer_name[0]->emailAddress->emailAddress;
				if ( $new_user_email == '' ) {
					$new_user_email = str_replace('@agora-middleware.com', '@agora-middleware-UPDATED.com', $user->user_email);
				}

				$query = $wpdb->prepare( 
					"UPDATE wp_users SET user_login = %s, display_name = %s, user_nicename = %s, user_email = %s WHERE user_login = %s", 
					$new_user_login, 
					$new_display_name, 
					$new_user_nicename,
					$new_user_email,
					$customer_number 
				);
				$wpdb->query( $query );

				echo 'UPDATED TO: '. $new_user_login . "\n";

				// Clear some caches, just in case.
				wp_cache_delete( $user->ID, 'users' );
				wp_cache_delete( $user->user_login, 'userlogins' );
				wp_cache_delete( sanitize_user( $new_user_login ), 'userlogins' );

			// There was a problem. Note the error.
			} else {

				echo "ERROR INFORMATION: \n";
				echo "update_user_name: \n";
				print_r($update_user_name);
				echo "update_customer_name: \n";
				print_r($update_customer_name);

				$query = $wpdb->prepare( 
					"UPDATE wp_users SET user_email = %s WHERE user_login = %s", 
					str_replace('@agora-middleware.com', '@agora-middleware-ERROR.com', $user->user_email),
					$user->user_login 
				);
				$wpdb->query( $query );
			}

		}

	}

	/**
	 * These are dirty, ugly hacks to enable get_users() to actually get all users. 
	 * They are neessary because a wp_capabilities meta tag is being searched, but many of the older
	 * Middleware users don't have this. So that check is being removed as well as the metatdata JOIN clause.
	 */
	function re_parse_query ( $user_query ) {
		$user_query->query_where = str_replace( "AND (wp_usermeta.meta_key = 'wp_capabilities' )", '', $user_query->query_where );
		$user_query->query_from = str_replace ( "INNER JOIN wp_usermeta ON (wp_users.ID = wp_usermeta.user_id)", '', $user_query->query_from );
		return $user_query;
	}

}
