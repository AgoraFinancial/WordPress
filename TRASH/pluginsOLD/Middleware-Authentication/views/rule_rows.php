<?php if(isset($auth_rules)): foreach($auth_rules as $r):?>
	<tr id="rule_<?php echo $rule->id; ?>">
		<td class="field-name"><?php echo $r->name; ?></td>
		<td class="field-type"><?php echo $r->field_group;?></td>
		<td class="field"><?php echo $r->field; ?></td>
		<td class="operator"><?php echo $r->readable_operator(); ?></td>
		<td class="value"><?php echo $r->value; ?></td>
		<td>
			<a href="#" class="edit_link" data-row-id="rule_editor_<?php echo $r->id; ?>">Edit</a> |
			<a href="#" class="delete_item" data-object-id="<?php echo $r->id;?>" data-object-type="rule" data-nonce="<?php echo wp_create_nonce('agora_authentication_nonce'); ?>">Delete</a>
		</td>
	</tr>
	<tr class="ajax-editor hidden" id="rule_editor_<?php echo $r->id;?>">
		<td colspan="6">
			<?php
				$id = "edit_rule_$rule->id";
				$action = 'rule_update';
				include('rule_form.php');
			?>
		</td>

	</tr>
<?php
	endforeach;
	unset($r);
	endif;
?>