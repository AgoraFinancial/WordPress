<?php
global $af_theme;

$fields = $af_theme->af_get_acf_fields($post->ID);
$sidebar_content = $fields['sidebar_content'];
?>
<main class="content-wrapper layout-sidebar-left">
    <div class="row">
        <div class="small-12 medium-4 columns">
            <aside>
                <?php echo $sidebar_content; ?>
            </aside>
        </div>
        <div class="small-12 medium-8 columns">
            <article>
                <?php echo apply_filters('the_content', $post->post_content); ?>
            </article>
        </div>
    </div>
</main>