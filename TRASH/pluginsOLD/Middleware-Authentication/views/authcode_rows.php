<?php foreach($all_pubcodes as $authcode): ?>
	<tr id="PubCode_<?php echo $authcode->term_id; ?>">
		<td class="auth_code" id="PubCode_<?php echo $authcode->term_id;?>_code">
			<span><?php echo $authcode->name; ?></span>
		</td>
		<td class="advantage_code">
			<span><?php echo $authcode->advantage_code; ?></span>
		</td>
		<td class="auth_type">
			<span><?php echo $auth_types[$authcode->type]; ?></span>
		</td>
		<td class="auth_description" id="PubCode_<?php echo $authcode->term_id; ?>_desc">
			<span><?php echo $authcode->description; ?></span>
		</td>
		<td class="auth_edit">
			<a href="#" class="edit_link" data-row-id="pubcode_editor_<?php echo $authcode->term_id; ?>">Edit</a> |
			<a href="#" class="delete_item" id="delete_<?php echo $authcode->name; ?>" data-object-id="<?php echo $authcode->term_id; ?>" data-object-type="authcode" data-nonce="<?php echo wp_create_nonce('agora_authentication_nonce'); ?>">Delete</a>
		</td>
	</tr>
	<?php
		$hidden = 'hidden';
		if(isset($current_authcode)){
			$hidden = ($current_authcode->term_id == $authcode->term_id) ? '' : 'hidden';
		}
	?>
	<tr class="ajax-editor <?php echo $hidden; ?>" id="pubcode_editor_<?php echo $authcode->term_id; ?>">
		<td colspan="5">
			<form action="admin-ajax.php" method="post" id="edit_pubcode_<?php echo $authcode->term_id; ?>" class="pubcode_ajax_form update" data-container="all_pubcodes_rows">
				<?php
				$disabled = true;
				$add_new = false;
				include('authcode_form.php'); ?>
			</form>
			<div class="clear"></div>
			<div class="input-group authcode_rules" id="authcode_<?php echo $authcode->term_id; ?>_rules ul">
				<h4><?php if($rules = $authcode->get_rules()){ echo 'Rules for this Authentication code'; }else { echo 'There are no rules for this Authentication Code';} ?></h4>
				<ul>
					<?php if($rules): foreach($rules as $k => $r): ?>
						<li class="authcode_rule">
							<span class="if"><?php _e('If');?></span>
							<span class="field"><?php echo $r->field; ?></span>
							<span class="operator"><?php echo $r->readable_operator(); ?></span>
							<span class="value"><?php if(!empty($r->shortcode)){ echo $r->shortcode; }else{ echo $r->value; } ?></span>
							<a class="delete_rule delete_item" data-object-id="<?php echo $k ?>" data-parent="<?php echo $authcode->term_id; ?>" href="#" data-nonce="<?php echo wp_create_nonce('agora_authentication_nonce'); ?>" data-object-type="rule">&nbsp;</a>
						</li>
					<?php endforeach; endif; ?>
				</ul>
			</div>
			<input type="button" name="add_rule" value="Add Rule" class="button-secondary right add_rule" data-authcode="<?php echo $authcode->term_id;?>">
		</td>
	</tr>
<?php
	endforeach;
	unset($authcode);
?>