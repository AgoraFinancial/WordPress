<?php
$term = get_queried_object();
$free_newsletter_logo = get_field('free_newsletter_logo', $term);
$free_newsletter_logo = wp_get_attachment_image($free_newsletter_logo['id'], 'medium_large');
?>
<header class="masthead-wrapper">
    <div class="row">
        <div class="small-12 columns">
            <div class="masthead">
                <h1>
                    <?php
                    // Get free newsletter logo
                    if ($free_newsletter_logo) {
                        echo $free_newsletter_logo;
                    } else {
                        echo $term->name;
                    }
                    ?>
                </h1>
            </div>
        </div>
    </div>
</header>