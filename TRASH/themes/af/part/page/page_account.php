<?php
global $af_theme, $af_users, $af_templates;

$fields = $af_theme->af_get_acf_fields($post->ID);
$update_password_instructions = isset($fields['update_password_instructions']) ? $fields['update_password_instructions'] : '';
$active_subscription_instructions = isset($fields['active_subscription_instructions']) ? $fields['active_subscription_instructions'] : '';
$personal_information_instructions = isset($fields['personal_information_instructions']) ? $fields['personal_information_instructions'] : '';

// Get optional location endpoint
$target = isset($_GET['key']) && $_GET['key'] ? $_GET['key'] : '' ;
?>

<main class="content-wrapper">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <div class="accordion-section">
                <h3 class="h4 accordion-header">Update My Password</h3>
                <div class="accordion-content" id="update-password-content">
                    <?php
                    if ($update_password_instructions)  {
                        echo $update_password_instructions;
                    }
                    $af_templates->af_password_form();
                    ?>
                </div>
            </div>
            <div class="accordion-section">
                <h3 class="h4 accordion-header">My Active Subscription Details</h3>
                <div class="accordion-content" id="account-subscription-table">
                    <?php
                    if ($active_subscription_instructions) {
                        echo $active_subscription_instructions;
                    }
                    $af_templates->af_subscription_table();
                    ?>
                </div>
            </div>
            <div class="accordion-section">
                <h3 class="h4 accordion-header">My Personal Information</h3>
                <div class="accordion-content" id="update-address-content">
                    <?php
                    if ($personal_information_instructions) {
                        echo $personal_information_instructions;
                    }
                    $af_templates->af_address_form();
                    ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php // Display target section by endpoint key
if($target) echo '<script>document.getElementById("' . $target . '").style.display = "block";console.log("' . $target . '");</script>'; ?>