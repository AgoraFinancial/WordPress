<div id="taxonomy-pubcodes" class="categorydiv">
	<ul id="pubcode-tabs" class="category-tabs">
		<li class="tabs">
			<a href="#pubcodes-all">All Pubcodes</a>
		</li>
		<li class="hide-if-no-js">
			<a href="#shortcodes-all">Shortcode</a>
		</li>
	</ul>

	<div id="pubcodes-all" class="tabs-panel" style="max-height: none;">
		<p>
			<strong>
				<?php _e('Assign this post to a Authentication Code.');?>
			</strong>
		</p>

		<p>
			<?php _e('Click into the box to reveal pubcode dropdown.');?>
		</p>

		<p>
			<?php _e('Begin typing to narrow your results');?>
		</p>

		<select id="pubcodeschecklist"  name="post_pubcode[]" multiple
				class="multipleSelect" style="display:none;" >
			<?php if( is_array($all_pubcodes) ) : foreach($all_pubcodes as $code):
				if(is_array($post_pubcodes) AND array_key_exists( $code->term_id, $post_pubcodes )) {
					$chk = 'selected';
				}else{
					$chk = '';
				}
				?>
				<option <?php echo $chk; ?> id="pubcode_<?php echo $code->name; ?>"
					value="<?php echo $code->term_id; ?>"><?php echo $code->name . ' - ' . $code->description; ?></option>
			<?php endforeach; endif; ?>
		</select>

	</div>
	<div id="shortcodes-all" class="tabs-panel" style="display: none; max-height: none;">
		<p>
			<?php _e('Use a shortcode to hide sections of content based on authcodes.');?>
		</p>

		<p>
			<?php _e('Click into the box to reveal pubcode dropdown.');?>
		</p>

		<p>
			<?php _e('Begin typing to narrow your results');?>
		</p>

		<select id="pubcodeschecklist"  name="post_pubcode[]" multiple
				class="list:category categorychecklist form-no-clear multipleSelect" style="display:none;" >
			<?php if( is_array($all_pubcodes) ) : foreach($all_pubcodes as $code): ?>
				<option <?php echo $chk; ?> id="<?php echo $code->name; ?>"
					value="<?php echo $code->name; ?>"><?php echo $code->name . ' - ' . $code->description; ?></option>
			<?php endforeach; endif; ?>
		</select>

		<div>
			<p>
				<strong>
					Copy and paste the shortcode below:
				</strong>
			</p>
			<p style="background: #f5f5f5; display: inline-block;">
				<input type="text" id="pubcodes" value='[hidecontent pubcodes=""] [/hidecontent]' style="font-size: 10pt;" size="42" >
				<br>
			</p>
		</div>
	</div>
</div>
