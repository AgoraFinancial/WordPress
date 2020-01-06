<h3><?php _e('Facebook Login Configuration'); ?></h3>
<p>You can find more information <strong><a href="http://docs.threefoldsystems.com:8090/display/shareit/5669462/XWA9a0b02bde22a4246b0ad615d6ff95425BZH" target="_blank">here</a></strong>.</p>
<div id="fb_app_settings">

	<form method="post" action="options.php">
		<?php settings_fields( $social_config_name . '_group' );?>
		<table class="form-table widefat">
			<tbody>
				<tr>
					<th scope="row" class="field_title">
						<?php _e('Enable Facebook Login'); ?>
					</th>

					<td>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $social_config_name;?>[fb_login_enable]" value="1" <?php if($social_config['fb_login_enable'] == 1) echo 'checked'; ?>>
								<?php _e('On'); ?>
							</label>

							<br>

							<label>
								<input type="radio" name="<?php echo $social_config_name;?>[fb_login_enable]" value="0" <?php if($social_config['fb_login_enable'] == 0) echo 'checked'; ?>>
								<?php _e('Off'); ?>
							</label>

							<br>

							<p class="description">
								Enables Facebook Login on your website (https is required for this).
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row" class="field_title">
						<?php _e('Facebook App Secret Key'); ?>
					</th>
					<td>
						<div class="fb_app_secret_field">
							<input class="authcode_name" type="text" name="<?php echo $social_config_name;?>[fb_app_secret]" value="<?php echo (isset($social_config['fb_app_secret'])) ? $social_config['fb_app_secret'] : null;?>" placeholder="<?php _e('Insert your APP Secret Key'); ?>">

							<p class="description">
								Insert your Facebook secret Key.
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row" class="field_title">
						<?php _e('Facebook APP ID'); ?>
					</th>

					<td>
						<div class="fb_app_id_field">
							<input class="authcode_name" type="text" name="<?php echo $social_config_name;?>[fb_app_id]" value="<?php echo (isset($social_config['fb_app_id'])) ? $social_config['fb_app_id'] : null;?>" placeholder="<?php _e('Insert your APP ID'); ?>">

							<p class="description">
								Insert your Facebook APP ID.
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row" class="field_title">
						<?php _e('Facebook API Version'); ?>
					</th>

					<td>
						<div class="fb_api_version_field">
							<input class="authcode_name" type="text" name="<?php echo $social_config_name;?>[fb_api_version]" value="<?php echo (isset($social_config['fb_api_version'])) ? $social_config['fb_api_version'] : null;?>" placeholder="<?php _e('Enter an API version number'); ?>">

							<p class="description">
								Enter a Facebook API version number.
							</p>
						</div>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="save_button"><?php submit_button(__( 'Save'), 'primary', 'submit'); ?></td>
				</tr>

			</tbody>
		</table>
	</form>
</div>