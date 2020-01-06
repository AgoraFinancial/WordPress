<form action="admin-ajax.php" class="rules_form" id="<?php echo $nonce = wp_create_nonce('agora_authentication_nonce'); ?>" data-container="all_pubcodes_rows">
	<div class="input-group">
		<label for="">If</label>
		<select name="field" id="field_name_<?php echo (isset($r)) ? $r->field : '';?>">
			<?php
				foreach($fields[$authcode->type] as $key => $value){
					$selected = '';
					echo '<option value="'. $value['path'] .'" '. $selected .'>'. $key .'</option>';
				}
			?>
		</select>
		<select name="operator" id="operator">
			<?php
				foreach(agora_auth_rule::$operators as $op => $readable):
					$selected = '';
					if(isset($r) AND $r->operator == $op){
						$selected = 'selected';
					}
			?>
				<option value="<?php echo $op; ?>" <?php echo $selected; ?>>
					<?php echo $readable; ?>
				</option>
			<?php
				endforeach;
			?>
		</select>
		<input type="hidden" name="action" value="<?php echo (isset($r)) ? 'rule_update' : 'rule_create'; ?>">
		<input type="hidden" name="authcode_id" value="<?php echo $authcode->term_id; ?>">
		<input type="hidden" name="field_group" value="<?php echo $authcode->type ;?>">
		<input type="hidden" name="security" value="<?php echo $nonce; ?>">
		<input type="text" name="value" id="value" value="<?php echo (isset($r)) ? $r->value : ''; ?>">
		<input type="submit" name="submit" value="<?php _e('Save'); ?>" class="button-primary" />
		<input type="button" name="cancel" value="cancel" class="button-secondary cancel_rule_form" data-cancel="<?php echo $nonce;?>">
	</div>
</form>