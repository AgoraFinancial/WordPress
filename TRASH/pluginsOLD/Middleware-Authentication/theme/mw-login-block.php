<!--
	It is highly recommended that you *copy* this file to your theme folder.
	Styles should be moved to your main stylesheet.
	Leave this template in the /theme/ folder of the plugin for future reference

	#Version: 2.0
-->

<div class="tfs-mw-wrapper-block">
	<?php
	if ( ! empty ( $title ) ) {
		?>
		<div id="tfs-mw-wrapper-block-title">
			<h2><?php echo $title; ?></h2>
		</div>
		<?php
	}
	?>

	<div class="<?php echo ( ! empty ( $message_class ) ) ? $message_class : ''; ?>">
		<div class="tfs-mw-wrapper-block-messages">
			<?php
			if ( ! empty( $subtitle ) ) {
				?>
				<p class="tfs-mw-wrapper-block-subtitle"><?php echo $subtitle; ?></p>
				<?php
			}

			if ( ! empty( $message_class ) && $message_class == 'error' ) {
				?>
				<p class="tfs-mw-wrapper-block-error"><?php echo $message; ?></p>
				<?php
			}
			?>
		</div>
	</div>

	<?php
	// Used for generating unique IDs for labels to work - multiple login forms on the same page.
	$form_get_time = mt_rand();
	?>
	<form name="loginform" id="tfs-mw-login-form-<?php echo $form_get_time; ?>" class="tfs-mw-loginform" action="<?php echo site_url( '/wp-login.php'); ?>" method="post">
		<div class="login-username">
			<label for="tfs-mw-user-login_<?php echo $form_get_time; ?>"><?php echo $form_parameters[ 'label_username' ]; ?></label>
			<input type="text" name="log" id="tfs-mw-user-login_<?php echo $form_get_time; ?>" class="input valid tfs-mw-user-login" value="<?php echo $form_parameters[ 'value_username' ]; ?>" size="20" placeholder="<?php echo $form_parameters[ 'username_placeholder' ]; ?>">
		</div>

		<div class="login-password">
			<label for="tfs-mw-user-pass_<?php echo $form_get_time; ?>"><?php echo $form_parameters[ 'label_password' ]; ?></label>

			<div class="mw-password-field-masking-container">
				<input type="password" name="pwd" id="tfs-mw-user-pass_<?php echo $form_get_time; ?>" class="input valid tfs-mw-user-pass" size="20" placeholder="<?php echo $form_parameters[ 'password_placeholder' ]; ?>">

				<span class="mw-password-field-masking mw-password-unmask" data-masking-input-id="tfs-mw-user-pass_<?php echo $form_get_time; ?>"></span>
			</div>
		</div>

		<div class="tfs-mw-wrapper-block-row">
			<div class="login-remember">
				<label>
					<input name="rememberme" type="checkbox" class="tfs-mw-rememberme" value="forever"> <?php echo $form_parameters[ 'label_remember' ]; ?>
				</label>
			</div>

			<?php
			if ( $forgot_password_link_short ) {
				$forgot_password_url = add_query_arg(
					array(
						'forgot' => 'password'
					),
					get_permalink( get_page_by_path( 'login/forgot-password' ) )
				);
				?>
				<div class="login-forgot-password-link">
					<a href="<?php echo $forgot_password_url; ?>"><?php echo $forgot_password_link_short; ?></a>
				</div>
				<?php
			}
			?>
		</div>

		<div class="login-submit">
			<input type="submit" name="wp-submit" class="button button-primary tfs-mw-wp-submit" value="<?php echo $form_parameters[ 'label_log_in' ]; ?>" data-login-form-id="tfs-mw-login-form-<?php echo $form_get_time; ?>">
			<input type="hidden" name="redirect_to" value="<?php echo $form_parameters[ 'redirect' ]; ?>">
		</div>
	</form>

	<?php
	if ( shortcode_exists( 'fb_login_shortcode' ) ) {
		echo do_shortcode('[fb_login_shortcode]');
	}

	if ( ! empty( $forgot_username_link ) ) {
		$forgot_username_url = add_query_arg(
			array(
				'forgot' => 'username'
			),
			get_permalink( get_page_by_path( 'login/forgot-password' ) )
		);
		?>
		<div class="tfs-mw-wrapper-block-forgot-username">
			<a href="<?php echo $forgot_username_url; ?>"><?php echo $forgot_username_link; ?></a>
		</div>
		<?php
	}
	?>

</div>
