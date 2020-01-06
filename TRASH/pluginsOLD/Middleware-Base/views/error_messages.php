<?php
if ( is_array( $validator ) ) {
	?>
	<ul>
		<?php foreach($validator as $v): ?>
			<li class="<?php echo $v->field; ?>">
				<?php echo $v->message; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}
?>
