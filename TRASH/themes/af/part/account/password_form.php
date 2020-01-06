<?php
global $af_users;

// Declare empty variables
$form = $err_current = $err_new = $valid_current = $valid_new = $message = '';

// Submitting form to self
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form'] == 'updatePassword') {

    // Open accordion for this form
    echo '<script>document.getElementById("update-password-content").style.display = "block";</script>';

    // Create update array
    $user_data = $af_users->af_get_user_data();
    $update_data = array(
        'customerNumber' => $user_data['advantage_id'],
        'username' => $user_data['username'],
    );

    $current_pass = isset($_POST['mw_current']) && $_POST['mw_current'] ? $_POST['mw_current'] : '';
    $new_pass = isset($_POST['mw_new']) && $_POST['mw_new'] ? $_POST['mw_new'] : '';
    $confirm_pass = isset($_POST['mw_confirm']) && $_POST['mw_confirm'] ? $_POST['mw_confirm'] : '';

    // Current password
    if (empty($current_pass)) {
        $err_current = 'Current password is required.';
    } else {
        // Verify current password is legit

        $user = get_user_by('login', $user_data['username']);
        if ($user && wp_check_password($current_pass, $user->data->user_pass)) {
            $valid_current = true;
            $update_data['existingPassword'] = $current_pass;
        } else {
            $err_current = 'Current password is incorrect.';
        }
    }

    // New password
    if (empty($new_pass) || empty($confirm_pass)) {
        $err_new = 'Both fields are required.';
    } elseif ($new_pass != $confirm_pass) {
        $err_new = 'The "Confirm New Password" field does not match the "New Password" field.';
    } elseif (strlen($new_pass) < 6 || strlen($confirm_pass) < 6) {
        $err_new = 'Must be a least 6 characters.';
    } elseif ($current_pass == $new_pass) {
        $err_new = 'Cannot reuse old password.';
    } else {
        if (preg_match('/"/', $new_pass) || preg_match('/\\/', $new_pass)) {
            $err_new = 'Cannot use quotations marks ( " ) or backslashes ( \ ) in password.';
        } else {
            $valid_new = true;
            $update_data['newPassword'] = $af_users->af_sanitize_input($new_pass);
            $update_data['newPassword2'] = $af_users->af_sanitize_input($confirm_pass);
        }
    }

    // If all fields are valid
    if ($valid_current && $valid_new) {

        // Run updater
        $run_update = $af_users->frmUpdateCustomerPassword($update_data);

        // Check for errors
        if (is_scalar($run_update) && $run_update != '') {
            $message = 'There was a problem updating your account. Please try double check your information and try again, or contact customer support for further assistance.';
            $message = $af_users->af_callout_msg($message, 'alert');
        } else {
            $user_data['advantage_email'] = $mw_email;
            $message = 'Success! Your password has been updated. It may take over <strong>10 minutes</strong> for this change to take effect.';
            $message = $af_users->af_callout_msg($message, 'success');
        }
    }
}
echo $message;
?>
<form class="form-account form-password" method="post">
    <input type="hidden" name="form" value="updatePassword">
    <!--current-->
    <div class="row">
        <div class="small-12 medium-4 columns">
            <label for="current_password">Current Password:<span class="required">*</span></label>
        </div>
        <div class="small-12 medium-8 columns">
            <input type="password" id="current_password"<?php if ($err_current) echo ' class="input-error"'; ?> name="mw_current" value="">
            <?php $af_users->af_display_input_error($err_current); ?>
        </div>
    </div>
    <!--new-->
    <div class="row">
        <div class="small-12 medium-4 columns">
            <label for="new_password">New Password:<span class="required">*</span></label>
        </div>
        <div class="small-12 medium-8 columns">
            <input type="password" id="new_password"<?php if ($err_new) echo ' class="input-error"'; ?> name="mw_new" value="">
        </div>
    </div>
    <!--confirm-->
    <div class="row">
        <div class="small-12 medium-4 columns">
            <label for="confirm_password">Confirm New Password:<span class="required">*</span></label>
        </div>
        <div class="small-12 medium-8 columns">
            <input type="password" id="confirm_password"<?php if ($err_new) echo ' class="input-error"'; ?> name="mw_confirm" value="">
            <?php $af_users->af_display_input_error($err_new); ?>
            <input type="submit" class="button" value="Save Changes">
        </div>
    </div>
</form>