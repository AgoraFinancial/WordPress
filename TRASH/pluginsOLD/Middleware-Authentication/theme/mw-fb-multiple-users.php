<!--
	It is highly recommended that you *copy* this file to your theme folder.
	Styles should be moved to your main stylesheet.
	Leave this template in the /theme/ folder of the plugin for future reference

	#Version: 2.0
-->

<div id="fb-multiple">
    <div class="<?php echo (isset($message_class)) ? $message_class : ''; ?>">
        <div class="ag_error"><?php if(isset($message)) echo $message; ?></div>
    </div>

    <div>
        <input type="hidden" name="data" value="<?php echo ''; ?>">
        <input type="text" name="username">
        <input type="submit">
    </div>
</div>