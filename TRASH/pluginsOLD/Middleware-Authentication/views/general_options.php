<div id="general_auth_settings">
	<form method="post" action="options.php">
		<?php settings_fields( $config_name . '_group' );?>

		<h3><?php _e('Display/Visual/UX'); ?></h3>

		<table class="form-table widefat">
			<tbody>
				<tr>
					<th scope="row" class="field_title">
						<?php _e('Display Before The Login Box?'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name; ?>[teaser]" value="none" <?php if( ! empty( $config['teaser'] ) && $config['teaser'] == 'none') echo 'checked'; ?>>
								<?php _e('Show no teaser'); ?>
							</label>

							<br/>

							<label>
								<input type="radio" name="<?php echo $config_name; ?>[teaser]" value="excerpt" <?php if( ! empty( $config['teaser'] ) && $config['teaser'] == 'excerpt') echo 'checked'; ?>>
								<?php _e('Show the Excerpt'); ?>
							</label>

							<br>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[teaser]" value="more_tag" <?php if( ! empty( $config['teaser'] ) && $config['teaser'] == 'more_tag') echo 'checked'; ?>>
								<?php _e('Show Content before the More Tag'); ?>
							</label>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row" class="field_title">
						<?php _e('Enable Customer Details In JS Format'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name;?>[show_user_js]" value="1" <?php if( ! empty( $config['show_user_js'] ) && $config['show_user_js'] == 1) echo 'checked'; ?>>
								<?php _e('On'); ?>
							</label>

							<br/>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[show_user_js]" value="0" <?php if( empty( $config['show_user_js'] ) ) echo 'checked'; ?>>
								<?php _e('Off'); ?>
							</label>

							<p class="description">
								Enable customer details (name, email, customer number) in JavaScript variables for use on front end.
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<th class="field_title" scope="row">
						<?php _e('HTML Emails'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name;?>[html_email]" value="1" <?php if( ! empty( $config['html_email'] ) && $config['html_email'] == 1) echo 'checked'; ?>>
								<?php _e('On');?>
							</label>

							<br>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[html_email]" value="0" <?php if( empty( $config['html_email'] ) ) echo 'checked'; ?>>
								<?php _e('Off'); ?>
							</label>

							<p class="description">
								Enable HTML content in your outbound emails.
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<th class="field_title" scope="row">
						<?php _e('Header Meta Tag With Pubcodes'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name;?>[header_meta_tag_pubcodes]" value="1" <?php if( ! empty( $config['header_meta_tag_pubcodes'] ) && $config['header_meta_tag_pubcodes'] == 1) echo 'checked'; ?>>
								<?php _e('On');?>
							</label>

							<br>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[header_meta_tag_pubcodes]" value="0" <?php if( empty( $config['header_meta_tag_pubcodes'] ) ) echo 'checked'; ?>>
								<?php _e('Off'); ?>
							</label>

							<p class="description">
								Enable 'mw-pubcodes' meta tag in the header on posts/pages that have pubcodes attached.
							</p>
						</div>
					</td>
				</tr>
			</tbody>
		</table>



		<h3 id="mw_auth_login_options_anchor"><?php _e('Login Options'); ?></h3>

		<table class="form-table widefat">
			<tbody>
				<tr>
					<th scope="row" class="field_title">
						<?php _e('Only Allow Valid User Login'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name;?>[valid_user_login]" value="1" <?php if ( ! empty( $config['valid_user_login'] ) && $config['valid_user_login'] == 1) echo 'checked'; ?>>
								<?php _e('On'); ?>
							</label>

							<br>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[valid_user_login]" value="0" <?php if ( empty( $config['valid_user_login'] )) echo 'checked'; ?>>
								<?php _e('Off'); ?>
							</label>

							<p class="description">
								Only allow users that have valid subscriptions to log in.
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row" class="field_title">
						<?php _e('Only Allow One Session At A Time Per User'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name;?>[one_login_session_at_a_time_per_user]" value="1" <?php if( ! empty( $config['one_login_session_at_a_time_per_user'] ) && $config['one_login_session_at_a_time_per_user'] == 1) echo 'checked'; ?>>
								<?php _e('On'); ?>
							</label>

							<br>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[one_login_session_at_a_time_per_user]" value="0" <?php if( empty( $config['one_login_session_at_a_time_per_user'] ) ) echo 'checked'; ?>>
								<?php _e('Off'); ?>
							</label>

							<p class="description">
								This will ensure that the same user is not logged in on multiple devices. It will automatically logout all other inactive sessions when the user is logged in a new session.
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row" class="field_title">
						<?php _e('Enable Duplicate Email'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name;?>[dup_user]" value="1" <?php if( ! empty( $config['dup_user'] ) && $config['dup_user'] == 1) echo 'checked'; ?>>
								<?php _e('On'); ?>
							</label>

							<br>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[dup_user]" value="0" <?php if( empty( $config['dup_user'] ) ) echo 'checked'; ?>>
								<?php _e('Off'); ?>
							</label>

							<p class="description">
								Enabling duplicate emails will allow two users who share the same email to login.
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row" class="field_title">
						<?php _e('Enable Cache Buster'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name;?>[cache_buster]" value="1" <?php if( ! empty( $config['cache_buster'] ) && $config['cache_buster'] == 1) echo 'checked'; ?>>
								<?php _e('On'); ?>
							</label>

							<br>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[cache_buster]" value="0" <?php if( empty( $config['cache_buster'] ) ) echo 'checked'; ?>>
								<?php _e('Off'); ?>
							</label>

							<p class="description">
								Enabling this will append a var to the url on failed login eg. ?login=failed.
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row" class="field_title">
						<?php _e('Enable Login Rate Limiting'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name;?>[rate_limiting]" value="1" <?php if( ! empty( $config['rate_limiting'] ) && $config['rate_limiting'] == 1) echo 'checked'; ?>>
								<?php _e('On'); ?>
							</label>

							<br/>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[rate_limiting]" value="0" <?php if( empty( $config['rate_limiting'] ) ) echo 'checked'; ?>>
								<?php _e('Off'); ?>
							</label>

							<p class="description">
								Prevents hacking attempts by blocking the IP of users who try to log in 50 times in one hour.
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row" class="field_title">
						<?php _e('Enable Message Central For Forgot Password'); ?>
					</th>

					<td>
						<a href="<?php echo admin_url( 'admin.php' ) . '?page=agora-mc'; ?>">Click Here to Configure Message Central Settings</a>
					</td>
				</tr>

				<tr>
					<th scope="row" class="field_title">
						<?php _e('Password Reset Mode'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name;?>[use_new_password_reset]" value="1" <?php if($config['use_new_password_reset'] == 1) echo 'checked'; ?>>
								Tokenized mode - Email a tokenized reset link (Recommended)
							</label>

							<br/>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[use_new_password_reset]" value="0" <?php if($config['use_new_password_reset'] == 0) echo 'checked'; ?>>
								<span style="color: red;">Legacy mode - Email a plain text password (Not Recommended)</span>
							</label>

							<p class="description">
								Allows password reset link to be sent to users.
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row" class="field_title">
						<?php _e('PubSvs Password Hashing'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name;?>[password_hashing]" value="1" <?php if( ! empty( $config['password_hashing'] ) && $config['password_hashing'] == 1) echo 'checked'; ?>>
								On (Password reset security level: High)
							</label>

							<br/>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[password_hashing]" value="0" <?php if( empty( $config['password_hashing'] ) ) echo 'checked'; ?>>
								Off (Password reset security level: Low)
							</label>

							<p class="description">
								Only enable this if PubSvs have hashed your passwords.
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row" class="field_title">
						<?php _e('Enable Secure Login/Magic Link For Password Reset'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name;?>[magic_link]" value="1" <?php if( ! empty( $config['magic_link'] ) && $config['magic_link'] == 1) echo 'checked'; ?>>
								<?php _e('On'); ?>
							</label>

							<br/>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[magic_link]" value="0" <?php if( empty( $config['magic_link'] ) ) echo 'checked'; ?>>
								<?php _e('Off'); ?>
							</label>

							<p class="description">
								Enable the use of a secure login link for forgot password. This mode sends the user a login link which allows them to log in the site without a password. More information <strong><a href="http://docs.threefoldsystems.com:8090/display/shareit/36601858/CUJfb9061aca2434c879b91eeb6d7ec4e13LBB" target="_blank">here</a></strong>.
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row" class="field_title">
						<?php _e('Trigger Password Reset Email On Failed Attempts'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name;?>[failed_login_email]" value="1" <?php if( ! empty( $config['failed_login_email'] ) && $config['failed_login_email'] == 1) echo 'checked'; ?>>
								<?php _e('On'); ?>
							</label>

							<br/>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[failed_login_email]" value="0" <?php if( empty( $config['failed_login_email'] ) ) echo 'checked'; ?>>
								<?php _e('Off'); ?>
							</label>

							<p class="description">
								Trigger a password reset email if the user fails their login a set number of times.
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row" class="field_title">
						<?php _e('Number Of Attempts To Trigger Email'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="number" name="<?php echo $config_name;?>[failed_login_number]" value="<?php echo ( ! empty( $config[ 'failed_login_number' ] ) ? $config[ 'failed_login_number' ] : '' ); ?>">
							</label>

							<p class="description">
								If the failed attempt emails are set to 'On', allow this number of attempts before sending the email.
							</p>
						</div>
					</td>
				</tr>


				<tr>
					<th scope="row" class="field_title">
						<?php _e('PDF File Authentication'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name;?>[auth_rewrite_htaccess]" value="1" <?php if( ! empty( $config[ 'auth_rewrite_htaccess' ] ) && $config['auth_rewrite_htaccess'] == 1) echo 'checked'; ?>>
								<?php _e('On'); ?>
							</label>

							<br/>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[auth_rewrite_htaccess]" value="0" <?php if( empty( $config[ 'auth_rewrite_htaccess' ] ) ) echo 'checked'; ?>>
								<?php _e('Off'); ?>
							</label>

							<p class="description">
								Enable PDF protection. More information <strong><a href="http://docs.threefoldsystems.com:8090/display/shareit/35946525/BXZ38192cd574b848ee8e3e7fec8d6091b6BXG" target="_blank">here</a></strong>.
							</p>
						</div>
					</td>
				</tr>
			</tbody>
		</table>



		<h3><?php _e('General'); ?></h3>

		<table class="form-table widefat">
			<tbody>
				<tr>
					<th scope="row" class="field_title">
						<?php _e('Extend User Session Expiration'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name;?>[no_expire]" value="1" <?php if( ! empty( $config[ 'no_expire' ] ) && $config['no_expire'] == 1) echo 'checked'; ?>>
								<?php _e('On'); ?>
							</label>

							<br/>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[no_expire]" value="0" <?php if( empty( $config[ 'no_expire' ] ) ) echo 'checked'; ?>>
								<?php _e('Off'); ?>
							</label>

							<p class="description">
								This will attempt to prevent users from being logged out by increasing the Wordpress authentication cookie expiration time.
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row" class="field_title">
						<?php _e('Create Authcode Custom Post Type'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $config_name;?>[mw_authcode_show]" value="1" <?php if( ! empty( $config[ 'mw_authcode_show' ] ) && $config['mw_authcode_show'] == 1) echo 'checked'; ?>>
								<?php _e('On'); ?>
							</label>

							<br/>

							<label>
								<input type="radio" name="<?php echo $config_name;?>[mw_authcode_show]" value="0" <?php if( empty( $config[ 'mw_authcode_show' ] ) ) echo 'checked'; ?>>
								<?php _e('Off'); ?>
							</label>

							<p class="description">
								Turn MW Authcode Custom Post Type - This can be used for Custom Menus / Dashboards etc. More information <strong><a href="http://docs.threefoldsystems.com:8090/display/shareit/36601911/USY9b27b513e1fd4dbfbeb20ffc0d2dfa61HKG" target="_blank">here</a></strong>.
							</p>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button(__( 'Save'), 'primary', 'submit'); ?>
	</form>
</div>
