<?php
global $af_users;

// Declare empty variables
$form = $mw_first_name = $mw_last_name = $mw_email = $mw_phone = $mw_address1 = $mw_address2 = $mw_city = $mw_state = $mw_zip = '';
$err_first_name = $err_last_name = $err_email = $err_phone = $err_address1 = $err_city = $err_state = $err_zip = '';
$valid_first_name = $valid_last_name = $valid_email = $valid_phone = $valid_address1 = $valid_city = $valid_state = $valid_zip = '';
$message = '';

// Get customer address info
$customer_info = $af_users->getCustomerAddress();
$key = key($customer_info);

// Set up prefilled values
$mw_address_code = $customer_info[$key]['addressCode'] ? $customer_info[$key]['addressCode'] : '';
$mw_first_name = $customer_info[$key]['firstName'] ? $customer_info[$key]['firstName'] : '';
$mw_last_name = $customer_info[$key]['lastName'] ? $customer_info[$key]['lastName'] : '';
$mw_email = $customer_info[$key]['emailAddress'] ? $customer_info[$key]['emailAddress'] : '';
$mw_phone = $customer_info[$key]['phoneNumber'] ? $customer_info[$key]['phoneNumber'] : '';
$mw_address1 = $customer_info[$key]['street'] ? $customer_info[$key]['street'] : '';
$mw_address2 = $customer_info[$key]['street2'] ? $customer_info[$key]['street2'] : '';
$mw_city = $customer_info[$key]['city'] ? $customer_info[$key]['city'] : '';
$mw_state = $customer_info[$key]['state'] ? $customer_info[$key]['state'] : '';
$mw_zip = $customer_info[$key]['postalCode'] ? $customer_info[$key]['postalCode'] : '';
if ($mw_zip) $mw_zip = substr($mw_zip, 0, 5); // Only display first 5 digits for customers

// Submitting form to self
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form'] == 'updateAddress') {

    // Open accordion for this form
    echo '<script>document.getElementById("update-address-content").style.display = "block";</script>';

    // Create update array
    $user_data = $af_users->af_get_user_data();
    $update_data = array(
        'customerNumber' => $user_data['advantage_id'],
        'addressCode' => $mw_address_code,
    );

    // First name
    if (empty($_POST['mw_first_name'])) {
        $err_first_name = 'First name is required.';
    } else {
        $mw_first_name = $af_users->af_sanitize_input($_POST['mw_first_name']);
        if (!preg_match('/^[a-zA-Z ]*$/', $mw_first_name)) {
            $err_first_name = 'Only letters and white spaces allowed';
        } else {
            $valid_first_name = true;
            $update_data['firstName'] = $mw_first_name;
        }
    }

    // Last name
    if (empty($_POST['mw_last_name'])) {
        $err_last_name = 'Last name is required.';
    } else {
        $mw_last_name = $af_users->af_sanitize_input($_POST['mw_last_name']);
        if (!preg_match('/^[a-zA-Z ]*$/', $mw_last_name)) {
            $err_last_name = 'Only letters and white spaces allowed';
        } else {
            $valid_last_name = true;
            $update_data['lastName'] = $mw_last_name;
        }
    }

    // Email
    if (empty($_POST['mw_email'])) {
        $err_email = 'Email address is required.';
    } else {
        $mw_email = $af_users->af_sanitize_input($_POST['mw_email']);
        if (!filter_var($mw_email, FILTER_VALIDATE_EMAIL)) {
            $err_email = 'Email address format is invalid.';
        } else {
            $valid_email = true;
            $update_data['emailAddress'] = $mw_email;
        }
    }

    // Phone
    if (!empty($_POST['mw_phone'])) {
        $mw_phone = $af_users->af_sanitize_input($_POST['mw_phone']);
        $pattern = '/^[+]?([\d]{0,3})?[\(\.\-\s]?([\d]{3})[\)\.\-\s]*([\d]{3})[\.\-\s]?([\d]{4})$/';
        if (!preg_match($pattern, $mw_phone)) {
            $err_phone = 'Phone number format is invalid.';
        } else {
            preg_match($pattern, $mw_phone, $matches);
            $mw_phone = $matches[2].'-'.$matches[3].'-'.$matches[4];
            $valid_phone = true;
            $update_data['phoneNumber'] = $mw_phone;
        }
    } else {
        $valid_phone = true;
    }

    // Address 1
    if (empty($_POST['mw_address1'])) {
        $err_address1 = 'Address is required.';
    } else {
        $mw_address1 = $af_users->af_sanitize_input($_POST['mw_address1']);
        $valid_address1 = true;
        $update_data['street'] = $mw_address1;

    }

    // Address 2
    if (!empty($_POST['mw_address2'])) {
        $mw_address2 = $af_users->af_sanitize_input($_POST['mw_address2']);
        $update_data['street2'] = $mw_address2;
    }

    // City
    if (empty($_POST['mw_city'])) {
        $err_city = 'City is required.';
    } else {
        $mw_city = $af_users->af_sanitize_input($_POST['mw_city']);
        $valid_city = true;
        $update_data['city'] = $mw_city;
    }

    // State
    if (empty($_POST['mw_state'])) {
        $err_state = 'State is required.';
    } else {
        $mw_state = $af_users->af_sanitize_input($_POST['mw_state']);
        $valid_state = true;
        $update_data['state'] = $mw_state;
    }

    // Zip
    if (empty($_POST['mw_zip'])) {
        $err_zip = 'Zip code is required.';
    } else {
        $mw_zip = $af_users->af_sanitize_input($_POST['mw_zip']);
        if (!preg_match('/^[0-9]{5,5}([- ]?[0-9]{4,4})?$/', $mw_zip)) {
            $err_zip = 'Zip code format is invalid.';
        } else {
            $valid_zip = true;
            $update_data['postalCode'] = $mw_zip;
        }
    }

    // If any errors found, set alert
    if ($err_first_name || $err_last_name || $err_email || $err_phone || $err_address1 || $err_city || $err_state || $err_zip) {
        $message = 'There was a problem with the information provided. Please see the highlighted fields below.';
        $message = $af_users->af_callout_msg($message, 'alert');
    }

    // If all fields are valid
    if ($valid_first_name && $valid_last_name && $valid_email && $valid_phone && $valid_address1 && $valid_city && $valid_state && $valid_zip) {

        // Run updater
        $run_update = $af_users->frmUpdateCustomerAddress($update_data);

        // Check for errors
        if (is_scalar($run_update) && $run_update != '') {
            $message = 'There was a problem updating your account. Please try double check your information and try again, or contact customer support for further assistance.';
            $message = $af_users->af_callout_msg($message, 'alert');
        } else {
            $message = 'Success! Your address has been updated. It may take over <strong>10 minutes</strong> for this change to take effect.';
            $message = $af_users->af_callout_msg($message, 'success');
            $af_users->af_reset_user_info( 900 );
        }
    }
}
echo $message;
?>
<form class="form-account form-personal-info" method="post">
    <input type="hidden" name="form" value="updateAddress">
    <!--name-->
    <div class="row">
        <div class="small-12 medium-4 columns">
            <label for="first_name">First Name:<span class="required">*</span></label>
        </div>
        <div class="small-12 medium-8 columns">
            <input type="text" id="first_name"<?php if ($err_first_name) echo ' class="input-error"'; ?> name="mw_first_name" value="<?php echo $mw_first_name; ?>" placeholder="First Name">
            <?php $af_users->af_display_input_error($err_first_name); ?>
        </div>
    </div>
    <div class="row">
        <div class="small-12 medium-4 columns">
            <label for="last_name">Last Name:<span class="required">*</span></label>
        </div>
        <div class="small-12 medium-8 columns">
            <input type="text" id="last_name"<?php if ($err_last_name) echo ' class="input-error"'; ?> name="mw_last_name" value="<?php echo $mw_last_name; ?>" placeholder="Last Name">
            <?php $af_users->af_display_input_error($err_last_name); ?>
        </div>
    </div>
    <!--email-->
    <div class="row">
        <div class="small-12 medium-4 columns">
            <label for="email">Email Address:<span class="required">*</span></label>
        </div>
        <div class="small-12 medium-8 columns">
            <input type="email" id="email"<?php if ($err_email) echo ' class="input-error"'; ?> name="mw_email" value="<?php echo $mw_email; ?>" placeholder="Email Address">
            <?php $af_users->af_display_input_error($err_email); ?>
        </div>
    </div>
    <!--phone-->
    <div class="row">
        <div class="small-12 medium-4 columns">
            <label for="phone">Phone:</label>
        </div>
        <div class="small-12 medium-8 columns">
            <input type="tel" id="phone"<?php if ($err_phone) echo 'class="input-error"'; ?> name="mw_phone" value="<?php echo $mw_phone; ?>" placeholder="Phone">
            <?php $af_users->af_display_input_error($err_phone); ?>
        </div>
    </div>
    <!--address 1-->
    <div class="row">
        <div class="small-12 medium-4 columns">
            <label for="address1">Address 1:<span class="required">*</span></label>
        </div>
        <div class="small-12 medium-8 columns">
            <input type="text" id="address1"<?php if ($err_address1) echo 'class="input-error"'; ?> name="mw_address1" value="<?php echo $mw_address1; ?>" placeholder="Address 1">
            <?php $af_users->af_display_input_error($err_address1); ?>
        </div>
    </div>
    <!--address 2-->
    <div class="row">
        <div class="small-12 medium-4 columns">
            <label for="address2">Address 2:</label>
        </div>
        <div class="small-12 medium-8 columns">
            <input type="text" id="address2" name="mw_address2" value="<?php echo $mw_address2; ?>" placeholder="Address 2">
        </div>
    </div>
    <!--city-->
    <div class="row">
        <div class="small-12 medium-4 columns">
            <label for="city">City:<span class="required">*</span></label>
        </div>
        <div class="small-12 medium-8 columns">
            <input type="text" id="city"<?php if ($err_city) echo ' class="input-error"'; ?> name="mw_city" value="<?php echo $mw_city; ?>" placeholder="City">
            <?php $af_users->af_display_input_error($err_city); ?>
        </div>
    </div>
    <!--state-->
    <div class="row">
        <div class="small-12 medium-4 columns">
            <label for="state">State:<span class="required">*</span></label>
        </div>
        <div class="small-12 medium-8 columns">
            <select id="state_selector"<?php if ($err_state) echo ' class="input-error"'; ?> name="mw_state">
                <option value=""<?php if ($mw_state == '') echo ' selected'; ?> disabled>Select a state</option>
                <option value="AL"<?php if ($mw_state == 'AL') echo ' selected'; ?>>Alabama</option>
                <option value="AK"<?php if ($mw_state == 'AK') echo ' selected'; ?>>Alaska</option>
                <option value="AZ"<?php if ($mw_state == 'AZ') echo ' selected'; ?>>Arizona</option>
                <option value="AR"<?php if ($mw_state == 'AR') echo ' selected'; ?>>Arkansas</option>
                <option value="CA"<?php if ($mw_state == 'CA') echo ' selected'; ?>>California</option>
                <option value="CO"<?php if ($mw_state == 'CO') echo ' selected'; ?>>Colorado</option>
                <option value="CT"<?php if ($mw_state == 'CT') echo ' selected'; ?>>Connecticut</option>
                <option value="DE"<?php if ($mw_state == 'DE') echo ' selected'; ?>>Delaware</option>
                <option value="DC"<?php if ($mw_state == 'DC') echo ' selected'; ?>>District Of Columbia</option>
                <option value="FL"<?php if ($mw_state == 'FL') echo ' selected'; ?>>Florida</option>
                <option value="GA"<?php if ($mw_state == 'GA') echo ' selected'; ?>>Georgia</option>
                <option value="HI"<?php if ($mw_state == 'HI') echo ' selected'; ?>>Hawaii</option>
                <option value="ID"<?php if ($mw_state == 'ID') echo ' selected'; ?>>Idaho</option>
                <option value="IL"<?php if ($mw_state == 'IL') echo ' selected'; ?>>Illinois</option>
                <option value="IN"<?php if ($mw_state == 'IN') echo ' selected'; ?>>Indiana</option>
                <option value="IA"<?php if ($mw_state == 'IA') echo ' selected'; ?>>Iowa</option>
                <option value="KS"<?php if ($mw_state == 'KS') echo ' selected'; ?>>Kansas</option>
                <option value="KY"<?php if ($mw_state == 'KY') echo ' selected'; ?>>Kentucky</option>
                <option value="LA"<?php if ($mw_state == 'LA') echo ' selected'; ?>>Louisiana</option>
                <option value="ME"<?php if ($mw_state == 'ME') echo ' selected'; ?>>Maine</option>
                <option value="MD"<?php if ($mw_state == 'MD') echo ' selected'; ?>>Maryland</option>
                <option value="MA"<?php if ($mw_state == 'MA') echo ' selected'; ?>>Massachusetts</option>
                <option value="MI"<?php if ($mw_state == 'MI') echo ' selected'; ?>>Michigan</option>
                <option value="MN"<?php if ($mw_state == 'MN') echo ' selected'; ?>>Minnesota</option>
                <option value="MS"<?php if ($mw_state == 'MS') echo ' selected'; ?>>Mississippi</option>
                <option value="MO"<?php if ($mw_state == 'MO') echo ' selected'; ?>>Missouri</option>
                <option value="MT"<?php if ($mw_state == 'MT') echo ' selected'; ?>>Montana</option>
                <option value="NE"<?php if ($mw_state == 'NE') echo ' selected'; ?>>Nebraska</option>
                <option value="NV"<?php if ($mw_state == 'NV') echo ' selected'; ?>>Nevada</option>
                <option value="NH"<?php if ($mw_state == 'NH') echo ' selected'; ?>>New Hampshire</option>
                <option value="NJ"<?php if ($mw_state == 'NJ') echo ' selected'; ?>>New Jersey</option>
                <option value="NM"<?php if ($mw_state == 'NM') echo ' selected'; ?>>New Mexico</option>
                <option value="NY"<?php if ($mw_state == 'NY') echo ' selected'; ?>>New York</option>
                <option value="NC"<?php if ($mw_state == 'NC') echo ' selected'; ?>>North Carolina</option>
                <option value="ND"<?php if ($mw_state == 'ND') echo ' selected'; ?>>North Dakota</option>
                <option value="OH"<?php if ($mw_state == 'OH') echo ' selected'; ?>>Ohio</option>
                <option value="OK"<?php if ($mw_state == 'OK') echo ' selected'; ?>>Oklahoma</option>
                <option value="OR"<?php if ($mw_state == 'OR') echo ' selected'; ?>>Oregon</option>
                <option value="PA"<?php if ($mw_state == 'PA') echo ' selected'; ?>>Pennsylvania</option>
                <option value="RI"<?php if ($mw_state == 'RI') echo ' selected'; ?>>Rhode Island</option>
                <option value="SC"<?php if ($mw_state == 'SC') echo ' selected'; ?>>South Carolina</option>
                <option value="SD"<?php if ($mw_state == 'SD') echo ' selected'; ?>>South Dakota</option>
                <option value="TN"<?php if ($mw_state == 'TN') echo ' selected'; ?>>Tennessee</option>
                <option value="TX"<?php if ($mw_state == 'TX') echo ' selected'; ?>>Texas</option>
                <option value="UT"<?php if ($mw_state == 'UT') echo ' selected'; ?>>Utah</option>
                <option value="VT"<?php if ($mw_state == 'VT') echo ' selected'; ?>>Vermont</option>
                <option value="VA"<?php if ($mw_state == 'VA') echo ' selected'; ?>>Virginia</option>
                <option value="WA"<?php if ($mw_state == 'WA') echo ' selected'; ?>>Washington</option>
                <option value="WV"<?php if ($mw_state == 'WV') echo ' selected'; ?>>West Virginia</option>
                <option value="WI"<?php if ($mw_state == 'WI') echo ' selected'; ?>>Wisconsin</option>
                <option value="WY"<?php if ($mw_state == 'WY') echo ' selected'; ?>>Wyoming</option>
                <option value="AS"<?php if ($mw_state == 'AS') echo ' selected'; ?>>American Samoa</option>
                <option value="GU"<?php if ($mw_state == 'GU') echo ' selected'; ?>>Guam</option>
                <option value="MP"<?php if ($mw_state == 'MP') echo ' selected'; ?>>Northern Mariana Islands</option>
                <option value="PR"<?php if ($mw_state == 'PR') echo ' selected'; ?>>Puerto Rico</option>
                <option value="UM"<?php if ($mw_state == 'UM') echo ' selected'; ?>>United States Minor Outlying Islands</option>
                <option value="VI"<?php if ($mw_state == 'VI') echo ' selected'; ?>>Virgin Islands</option>
                <option value="AA"<?php if ($mw_state == 'AA') echo ' selected'; ?>>Armed Forces Americas</option>
                <option value="AP"<?php if ($mw_state == 'AP') echo ' selected'; ?>>Armed Forces Pacific</option>
                <option value="AE"<?php if ($mw_state == 'AE') echo ' selected'; ?>>Armed Forces Others</option>
            </select>
            <?php $af_users->af_display_input_error($err_state); ?>
        </div>
    </div>
    <!--zip-->
    <div class="row">
        <div class="small-12 medium-4 columns">
            <label for="zip">Zip Code:<span class="required">*</span></label>
        </div>
        <div class="small-12 medium-8 columns">
            <input type="text" id="zip"<?php if ($err_zip) echo ' class="input-error"'; ?> name="mw_zip" value="<?php echo $mw_zip; ?>" placeholder="Zip">
            <?php $af_users->af_display_input_error($err_zip); ?>
            <input type="submit" class="button" value="Save Changes">
        </div>
    </div>
</form>