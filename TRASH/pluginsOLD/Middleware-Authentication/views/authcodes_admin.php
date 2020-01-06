<h3><?php _e('Authentication Codes'); ?></h3>
<div id="pubcodes_admin">
	<table id="all_pubcodes" class="widefat">

		<thead>
			<tr>
				<th class="auth_code"><?php _e('Auth Code'); ?></th>
				<th class="auth_advantage_code"><?php _e('Advantage Code');?></th>
				<th class="auth_type"><?php _e('Type'); ?></th>
				<th class="auth_description"><?php _e('Description'); ?></th>
				<th class="auth_edit"><?php _e('Edit'); ?></th>
			</tr>
		</thead>

		<tbody id="all_pubcodes_rows" class="sortable">
			<?php include('authcode_rows.php'); ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">
					<h3><?php _e('Add new Authentication Code'); ?></h3>
				</td>
			</tr>
			<tr>
				<td class="form-cell" colspan="5">
					<div class="ajax_message"></div>
					<form id="add_new_authcode" method="post" action="admin-ajax.php" class="pubcode_ajax_form" data-container="all_pubcodes_rows">
						<?php
						$disabled = false;
						$add_new = true;
						include('authcode_form.php');?>
					</form>
				</td>
			</tr>
		</tfoot>
	</table>
</div>