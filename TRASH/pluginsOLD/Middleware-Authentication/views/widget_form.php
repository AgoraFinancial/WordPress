<p>Use this widget to place a login box in your sidebars</p>
<p>
	<label for="<?php echo $logged_out['id']; ?>"><?php echo $logged_out['label']; ?></label> 
	<input class="widefat" id="<?php echo $logged_out['title']; ?>" name="<?php echo $logged_out['title']; ?>" type="text" value="<?php esc_attr_e( $logged_out['value'] ); ?>">
</p>
<p>
	<label for="<?php echo $logged_in['id']; ?>"><?php echo $logged_in['label']; ?></label> 
	<input class="widefat" id="<?php echo $logged_in['title']; ?>" name="<?php echo $logged_in['title']; ?>" type="text" value="<?php esc_attr_e( $logged_in['value'] ); ?>">
</p>