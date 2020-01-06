<form method="post" action="options.php">
<?php settings_fields( $config_name . '_group' );?>
<h3><?php _e('Event Tracking'); ?></h3>
<table class="form-table">
	<tbody>
	<tr>
		<th scope="row">
			<?php _e('Event tracking enabled:'); ?>
		</th>
		<td>
			<div class="radio">
				<label>
					<input type="radio" name="<?php echo $config_name;?>[eventing_enabled]" value="1" <?php if( ! empty( $config['eventing_enabled'] ) && $config['eventing_enabled'] == 1) echo 'checked'; ?>>
					<?php _e('On'); ?>
				</label>
				<br>
				<label>
					<input type="radio" name="<?php echo $config_name;?>[eventing_enabled]" value="0" <?php if( empty( $config['eventing_enabled'] ) ) echo 'checked'; ?>>
					<?php _e('Off'); ?>
				</label>
				<p class="description">
					Allow event tracking
				</p>
			</div>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="event_prod_token">
				<?php _e('Production Eventing Token'); ?>
			</label>
		</th>
		<td>
			<input type="text" value="<?php echo ( ! empty( $config[ 'event_prod_token' ] ) ? $config[ 'event_prod_token' ] : '' ); ?>" id="event_prod_token" name="<?php echo $config_name;?>[event_prod_token]" class="regular-text" placeholder="Production Eventing Token">
			<p class="description"><?php _e('A random text string needed to authenticate with the Eventing API. Contact Publishing Services to obtain your token'); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="prod_event_endpoint">
				<?php _e('Production Eventing API'); ?>
			</label>
		</th>
		<td>
			<input type="text" value="<?php echo ( ! empty( $config[ 'prod_event_endpoint' ] ) ? $config[ 'prod_event_endpoint' ] : '' ); ?>" id="prod_event_endpoint" name="<?php echo $config_name;?>[prod_event_endpoint]" class="regular-text">
			<p class="description"><?php _e('The URL for the Production Eventing API'); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="event_uat_token">
				<?php _e('UAT Eventing Token'); ?>
			</label>
		</th>
		<td>
			<input type="text" value="<?php echo ( ! empty( $config[ 'event_uat_token' ] ) ? $config[ 'event_uat_token' ] : '' ); ?>" id="event_uat_token" name="<?php echo $config_name;?>[event_uat_token]" class="regular-text" placeholder="UAT Eventing Token">
			<p class="description"><?php _e('A random text string needed to authenticate with the Eventing API. Contact Publishing Services to obtain your token'); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="uat_event_endpoint">
				<?php _e('UAT Eventing API'); ?>
			</label>
		</th>
		<td>
			<input type="text" value="<?php echo ( ! empty( $config[ 'uat_event_endpoint' ] ) ? $config[ 'uat_event_endpoint' ] : '' ); ?>" id="uat_event_endpoint" name="<?php echo $config_name;?>[uat_event_endpoint]" class="regular-text">
			<p class="description"><?php _e('The URL for the UAT Eventing API'); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="event_queue">
				<?php _e('Event Queue'); ?>
			</label>
		</th>
		<td>
			<input type="text" value="<?php echo ( ! empty( $config[ 'event_queue' ] ) ? $config[ 'event_queue' ] : '' ); ?>" id="event_queue" name="<?php echo $config_name;?>[event_queue]" class="regular-text" placeholder="Event Queue">
			<p class="description"><?php _e('The name of your event queue. Specific to your affiliate'); ?></p>
		</td>
	</tr>
	</tbody>
</table>
<?php submit_button(__( 'Save'), 'primary', 'submit'); ?>
</form>