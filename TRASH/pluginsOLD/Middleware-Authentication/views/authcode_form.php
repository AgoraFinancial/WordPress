	<div class="authcode_field name_field">
		<input class="authcode_name authcode_input" type="text" name="name" <?php echo $disabled === true ? 'disabled' : ''; ?>
			   value="<?php echo (isset($authcode)) ? $authcode->name : null;?>" placeholder="<?php _e('Authcode Name'); ?>">
		<div class = "tooltip">
			Pick a unique name for your Auth code
		</div>
	</div>
	<div class="authcode_field code_field">
		<input class="authcode_advantage_code authcode_input" type="text" name="advantage_code"
			   value="<?php echo (isset($authcode)) ? $authcode->advantage_code : ''; ?>" placeholder="<?php _e('Adv Code');?>">
		<div class = "tooltip">
			Enter the Advantage item code
		</div>
	</div>
	<div class="authcode_field type_field">
		<select name="type" class="authcode_type authcode_input">
			<?php
				if($add_new === true){
					echo '<option value="subscriptions">Authentication Type</option>';
				}
                foreach($auth_types as $key => $value):
                    $selected = '';
                    if(isset($authcode) AND $authcode->type == $key) $selected ='selected';
                    echo '<option value="' . $key . '" '. $selected.'>'. $value .'</option>';
                endforeach;
            ?>
		</select>
		 <div class = "tooltip">
			Choose what type of item code you want
		</div>
	</div>
	<div class="authcode_field desc_field">
		<input class="authcode_description authcode_input" type="text" name="description" value="<?php echo (isset($authcode)) ? $authcode->description : '';?>" size="50" placeholder="<?php _e('Description');?>">
		<div class = "tooltip">
			The description will tell your editors what the authcode does
		</div>
	</div>
	<div class="authcode_field submit_field">
		<input type="submit" name="submit" value="<?php echo (isset($authcode)) ? 'Save' : 'Add New'; ?>" class="button-primary" />
		<input type="hidden" name="action" value="<?php echo (isset($authcode)) ? 'authcode_update' : 'authcode_create'; ?>">
		<?php if(isset($authcode)):?>
			<input type="hidden" name="id" value="<?php echo $authcode->term_id; ?>">
		<?php endif; ?>
		<input type="hidden" name="security" value="<?php echo wp_create_nonce('agora_authentication_nonce'); ?>">
		<div class="ajax_spinner">&nbsp;</div>
	</div>

