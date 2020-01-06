<?php
global $af_templates;
$title = get_the_title();
?>
<main class="content-wrapper">
    <div class="row">
        <div class="small-12 medium-10 medium-centered columns">
            <article>
                <div class="question" id="<?php echo sanitize_title($title); ?>" pid="<?php echo get_the_ID(); ?>">
                    <h2><?php echo $title; ?></h2>
                    <?php echo apply_filters('the_content', $post->post_content); ?>
                    <hr>
                    <div class="helpful">
                        <p>Was this answer helpful?</p>
                        <p>
                            <a class="button faq-helpful" data-faq-helpful="yes">YES</a>
                            <a class="button secondary faq-helpful" data-faq-helpful="no">NO</a>
                        </p>
                    </div>
                </div>
                <?php $af_templates->af_faq_category_list(); ?>
            </article>
        </div>
    </div>
</main>