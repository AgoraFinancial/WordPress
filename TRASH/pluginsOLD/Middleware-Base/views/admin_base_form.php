<div class="grid_half">
<h3><?php _e('Global Settings'); ?></h3>

	<?php
	if ( isset( $_SESSION['agora_session_var']['Import'] ) ) {
		if ($_SESSION['agora_session_var']['Import']) {
			$status = $_SESSION['agora_session_var']['Import']['message']['status'];
			unset ($_SESSION['agora_session_var']['Import']['message']['status']);

			if ( is_array( $_SESSION['agora_session_var']['Import']['message'] ) ) {
				echo '<div class="notice notice-'.$status.'"> <ul>';
				foreach ($_SESSION['agora_session_var']['Import']['message'] as $message){
					echo '<li>'. $message .'</li>';
				}
				echo '</div> </ul>';
			}

			unset ($_SESSION['agora_session_var']['Import']);
		}
	}
	?>

<form method="post" action="options.php">
	<?php settings_fields( $config_name . '_group' );?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<?php _e('Connect to:'); ?>
				</th>
				<td>
					<div class="radio">
						<label>
							<input type="radio" name="<?php echo $config_name;?>[production]" value="1" <?php if( ! empty( $config['production'] ) && $config['production'] == 1) echo 'checked'; ?>>
							<?php _e('Production Environment'); ?>
						</label>
						<br>
						<label>
							<input type="radio" name="<?php echo $config_name;?>[production]" value="0" <?php if( empty( $config['production'] ) ) echo 'checked'; ?>>
							<?php _e('UAT Environment'); ?>
						</label>
					</div>
				</td>
			</tr>
		</tbody>
	</table>

	<h3><?php _e('Production Settings'); ?></h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label for="prod_token">
						<?php _e('Production Token'); ?>
					</label>
				</th>
				<td>
					<input type="text" value="<?php echo ( ! empty( $config[ 'prod_token' ] ) ? $config[ 'prod_token' ] : '' ); ?>" id="prod_token" name="<?php echo $config_name;?>[prod_token]" class="regular-text" placeholder="Production Token">
					<p class="description"><?php _e('A random text string needed to authenticate with middleware. Contact Publishing Services to obtain your token'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="prod_url">
						<?php _e('Production URL');?>
					</label>
				</th>
				<td>
					<input type="text" value="<?php echo ( ! empty( $config[ 'prod_url' ] ) ? $config[ 'prod_url' ] : '' ); ?>" id="prod_url" name="<?php echo $config_name;?>[prod_url]" class="regular-text">
					<p class="description"><?php _e('The URL for the Production middleware environment'); ?></p>
				</td>
			</tr>
		</tbody>
	</table>

	<h3><?php _e('UAT Settings'); ?></h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label for="uat_token">
						<?php _e('UAT Token');?>					
					</label>
				</th>
				<td>
					<input type="text" value="<?php echo ( ! empty( $config[ 'uat_token' ] ) ? $config[ 'uat_token' ] : '' ); ?>" id="uat_token" name="<?php echo $config_name;?>[uat_token]" class="regular-text" placeholder="UAT Token">
					<p class="description"><?php _e('A random text string needed to authenticate with middleware. Contact Publishing Services to obtain your token'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="uat_url">
						<?php _e('UAT URL');?>
					</label>
				</th>
				<td>
					<input type="text" value="<?php echo ( ! empty( $config[ 'uat_url' ] ) ? $config[ 'uat_url' ] : '' ); ?>" id="uat_url" name="<?php echo $config_name;?>[uat_url]" class="regular-text">
					<p class="description"><?php _e('The URL for the UAT middleware environment'); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
	<h3><?php _e('General Settings'); ?></h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<?php _e('Logging Enabled:'); ?>
				</th>
				<td>
					<div class="radio">
						<label>
							<input type="radio" name="<?php echo $config_name;?>[logging]" value="1" <?php if( ! empty( $config['logging'] ) && $config['logging'] == 1) echo 'checked'; ?>>
							<?php _e('On'); ?>
						</label>
						<br>
						<label>
							<input type="radio" name="<?php echo $config_name;?>[logging]" value="0" <?php if( empty( $config['logging'] ) ) echo 'checked'; ?>>
							<?php _e('Off'); ?>
						</label>
						<p class="description">
							Log events will be written to the default php error log
						</p>
					</div>
				</td>
			</tr>
            <tr>
                <th scope="row">
                    <?php _e('Affiliate Code:'); ?>
                </th>
                <td>
                    <div class="radio">

                            <label>
                                <input type="text"
                                       name="<?php echo $config_name;?>[affiliate_code]"
                                       value="<?php echo ( ! empty( $config[ 'affiliate_code' ] ) ? $config[ 'affiliate_code' ] : '' ); ?>" class="regular-text" placeholder="Affiliate Code">
                        </label>
                        <br>

                        <p class="description">
                            Affiliate / Vendor code - up to 12 chars used for payment calls.
                        </p>
                    </div>
                </td>
            </tr>
		</tbody>
	</table>

	<h3><?php _e('Caching Settings *Experimental*'); ?></h3>
	<table class="form-table">
		<tbody>
		<tr>
			<th scope="row">
				<?php _e('Caching Enabled:'); ?>
			</th>
			<td>
				<div class="radio">
					<label>
						<input type="radio" name="<?php echo $config_name;?>[caching]" value="1" <?php if( ! empty( $config['caching'] ) && $config['caching'] == 1) echo 'checked'; ?>>
						<?php _e('On'); ?>
					</label>
					<br>
					<label>
						<input type="radio" name="<?php echo $config_name;?>[caching]" value="0" <?php if( empty( $config['caching'] ) ) echo 'checked'; ?>>
						<?php _e('Off'); ?>
					</label>
					<p class="description">
						This is an experimental feature, we recommend leaving it switched off for - but if your adventurous go for it at your own risk.

						How it works

						This will cache ALL Middleware GET requests for 2 hours.

					</p>
				</div>
			</td>
		</tr>
		</tbody>
	</table>
	<?php submit_button(__( 'Save'), 'primary', 'submit'); ?>

	<hr />
</form>

	<table class="form-table">
		<tr>
			<th scope="row" class="import_export_row_base field_title">
				<?php _e('Import/Export Middleware Plugin Settings'); ?>
			</th>
			<td>
				<form id="exportForm" method="post" action="">
					<input type="submit" id="export_options_base" name="export_options_base" class="button button-primary" value="Export Settings">
				</form>
				<a data-remodal-target="import_options_base" id="import_options_base" class="button button-default">Import Settings</a>
			</td>
		</tr>
	</table>

	<!--Remodal for exporting Base settings-->
	<div class="remodal" data-remodal-id="export_options_base">
		<button data-remodal-action="close" class="remodal-close"></button>
		<h1>Export Middleware Plugin Settings</h1>
		<hr />

		<p>
			Copy the content from the textfield below, which can then be pasted into the import section's textfield when importing the settings.
			These settings can be used on any other WordPress set-up, as long as MiddleWare Base plugin versions match on both set-ups.
			It's best to ensure both WordPress set-ups are also on the same version to avoid import errors.
		</p>

		<textarea id="exported_settings_base"><?php echo do_shortcode('[export_options_mw]'); ?></textarea>
	</div>

	<!--Remodal for importing Base settings-->
	<div class="remodal" data-remodal-id="import_options_base">
		<button data-remodal-action="close" class="remodal-close"></button>
		<h1>Import Middleware Plugin Settings</h1>
		<hr />

		<p>
			Paste the content from the textfield you have copied from the export section.
			If importing settings from another WordPress set-up, ensure versions of MiddleWare Base plugin match on both set-ups.
			It's best to ensure both WordPress set-ups are also on the same version to avoid import errors.
		</p>

		<div class="message_import_base"></div>

		<form name="import_base_options" action="" method="post" enctype="multipart/form-data"">
			<input type="file" id="import_settings_base" name="import_options_base" accept="*.json">
			<br />
			<br />
			<input id="submit-import_base_options" class="button button-primary" type="submit" value="Submit"/>
			<?php
			// Setting nonce
			if(function_exists('wp_nonce_field')) { wp_nonce_field('nonce_import_base_options', 'nonce_import_base_options'); }
			?>
		</form>
	</div>
</div>

<div class="grid_half">
	<h3>External IP: <?php _e($external_ip); ?></h3>
	<p><?php _e('Your external IP will need to be cleared with Agora IT, if it changes you will need to notify Pub Services');?></p>
	<h3>
		<div class="indicator" id="status_light"></div>
		<span id="status_indicator"><?php _e('Checking Connection Status...');?></span>
	</h3>
	<p id="status_help"></p>
</div>