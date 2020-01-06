<?php
global $af_theme;

$fields = $af_theme->af_get_acf_fields($post->ID);

// Get form settings
$default_source = $fields['default_source'];
$email_oversight_list_id = $fields['email_oversight_list_id'];
$form_submit_url = $fields['form_submit_url'];
$submit_button_text = $fields['submit_button_text'];
$newsletter_subscriptions = $fields['newsletter_subscriptions'];

// Set defaults
$source = isset($_GET['code']) ? $_GET['code'] : $default_source;
$submit_button_text = $submit_button_text ? $submit_button_text : 'Subscribe';
?>
<main class="content-wrapper">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <article>
                <?php echo apply_filters('the_content', $post->post_content); ?>
                <div class="callout primary coreg-wrapper">
                    <h2 class="h3 coreg-form-title section-header">Recommended:</h2>
                    <script src="https://signup.agorafinancial.com/Scripts/CheckEmail.js"></script>
                    <form action="<?php echo $form_submit_url; ?>" id="LeadGen" method="post" onsubmit="return validateNewsletterForm()">
                        <input name="source" type="hidden" value="<?php echo $source; ?>">
                        <input name="NotSaveSignup" type="hidden" value="False">
                        <input name="ListID" type="hidden" value="<?php echo $email_oversight_list_id; ?>">
                        <?php
                        // Show newsletters
                        foreach ($newsletter_subscriptions as $sub) {
                            $id = $sub['id'];
                            $pubcode = $sub['pubcode'];
                            $title = $sub['title'];
                            $description = $sub['description'];
                        ?>
                        <label>
                            <input class="coreg-checkbox-input" name="CoRegs" checked="checked" type="checkbox" value="<?php echo $id; ?>" data-pubcode="<?php echo $pubcode; ?>">
                            <h3 class="h5 coreg-pub-title"><?php echo $title; ?></h3>
                        </label>
                        <?php
                            if ($description) {
                                echo '<p>' . $description . '</p>';
                            }
                        }
                        ?>
                        <input id="email" name="email" class="coreg-email-input" maxlength="255" type="email" placeholder="Enter Email Address">
                        <input id="form-submit" class="button large coreg-submit-button" type="submit" value="<?php echo $submit_button_text; ?>">
                    </form>
                </div>
            </article>
        </div>
    </div>
</main>