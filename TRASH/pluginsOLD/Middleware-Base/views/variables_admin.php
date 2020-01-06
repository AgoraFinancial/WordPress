<h3><?php _e('Text &amp; Language Variables'); ?></h3>
<p><?php _e('These snippets of text are used by the program in various places. Any text you do not see here that you want to change will probably be found in the theme files');?></p>
<form method="post" action="options.php">
	<?php settings_fields( $config_name . '_group' );?>
	<table class="form-table">
		<tbody>
			<?php
			if ( is_array( $config ) ) {
				foreach( $config as $config_item => $config_value ) {
					// Display heading if name contains 'heading'
					if ( strpos( $config_item, 'heading_' ) === 0 ) {
						?>
						<tr class="lv-table-heading">
							<td colspan="2">
								<h3><?php echo $config_value; ?></h3>
							</td>

							<input type="hidden" value="<?php echo htmlentities( $config_value ); ?>" name="<?php echo $config_name . '[' . $config_item . ']'; ?>">
						</tr>
						<?php
					} else {
						?>
						<tr>
							<th scope="row">
								<label for="<?php echo $config_item; ?>">
									<?php echo $config_item; ?>
								</label>
							</th>

							<td>
								<?php
								if ( strpos( $config_item, 'txt_' ) === 0 ) {
									?>
									<input type="text" value="<?php echo htmlentities($config_value); ?>" name="<?php echo $config_name . '[' . $config_item . ']'; ?>" id="<?php echo $config_item; ?>" class="large-text">
									<?php
								} elseif ( strpos( $config_item, 'inp_' ) === 0 ) {
									?>
									<textarea name="<?php echo $config_name . '[' . $config_item . ']'; ?>" id="<?php echo $config_item; ?>" class="large-text"><?php echo htmlentities($config_value); ?></textarea>
									<?php
								}
								?>
							</td>
						</tr>
						<?php
					}
				}
			}
			?>
		</tbody>
	</table>
	<?php submit_button(__( 'Save'), 'primary', 'submit'); ?>
	<hr />
</form>