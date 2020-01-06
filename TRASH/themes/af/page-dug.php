<?php
/*
Template Name: DUG
*/
$af_templates->af_head();
$af_templates->af_header();

if ( ! is_user_logged_in() ){
	get_template_part( 'templates/access-denied' );
} else {
	$allow_access = false;
	$support_userids = get_option( 'support_userids' );
	if ( $support_userids && ! empty( $support_userids ) ) {
		$support_userids = explode(',', $support_userids);
		if ( in_array( get_current_user_id(), $support_userids ) ) {
			$allow_access = true;
		}
	}
	if ( ! current_user_can('administrator') ) {
		wp_redirect(SOUTHBANK_RESEARCH_URL);
		die();
	} else { ?>
		<div class = "container">
			<div class = "row">
				<div class="col-md-12">
					<div class="panel">
						<?php dug_options_page(); ?>
					</div>
				</div>
			</div>
		</div>
	<?php 
	}
}
$af_templates->af_footer();