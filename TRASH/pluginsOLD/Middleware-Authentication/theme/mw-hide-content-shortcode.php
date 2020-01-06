<!--
	It is highly recommended that you *copy* this file to your theme folder.
	Styles should be moved to your main stylesheet.
	Leave this template in the /theme/ folder of the plugin for future reference

	#Version: 2.0
-->

<div id="mw_login">
    <p>
        <?php echo !empty($warning) ? $warning : 'To view this content you must have one of the following subscriptions:'; ?>
    </p>

    <?php
    if ( is_array( $authcodes ) ) {
        foreach($authcodes as $authcode){
            echo '- ' . $authcode->description . '<br>';
        }
    }
    ?>
</div>