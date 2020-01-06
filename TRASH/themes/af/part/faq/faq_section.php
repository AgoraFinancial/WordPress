<?php
global $af_faqs;

// If coming from publication FAQs, get post data, otherwise fall back to global $post
if (isset($faq) && !empty($faq)) {
    // If $faq is object, get ID, else use as ID
    $faq_id = is_array($faq) ? $faq->ID : $faq ;
    $post_data = get_post($faq_id);
    $faq_data = $af_faqs->af_single_faq_data($post_data);
    $faq_content = $post_data->post_content;
} else {
    $faq_data = $af_faqs->af_single_faq_data($post);
    $faq_content = $post->post_content;
} ?>

<div class="accordion-section question" id="<?php echo sanitize_title(get_the_title()); ?>" pid="<?php echo $faq_data['faq_id']; ?>">
    <h3 class="h4 accordion-header"><?php echo get_the_title($faq_data['faq_id']); ?></h3>
    <div class="accordion-content">
        <?php echo apply_filters('the_content', $faq_content); ?>
        <hr>
        <div class="helpful">
            <p>Was this answer helpful?<br>
                <a class="button tiny faq-helpful" data-faq-helpful="yes">YES</a>
                <a class="button secondary tiny faq-helpful" data-faq-helpful="no">NO</a>
            </p>
        </div>
    </div>
</div>