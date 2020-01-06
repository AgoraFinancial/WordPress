<?php
global $af_theme;

// Get path options
$fields = $af_theme->af_get_acf_fields($post->ID);
$path_options = $fields['path_options'];

if (shortcode_exists('first_name')) {
?>
<header class="masthead-wrapper">
    <div class="row">
        <div class="small-12 columns">
            <div class="masthead">
                <h1>Welcome, <?php echo do_shortcode('[first_name]'); ?></h1>
            </div>
        </div>
    </div>
</header>
<?php
}
?>
<main class="content-wrapper">
    <div class="row">
        <div class="small-12 columns">
            <article>
                <?php
                if ($post->post_content != '') {
                    echo apply_filters('the_content', $post->post_content);
                ?>
                <hr class="custom-separator">
                <?php
                }
                // Display all path options in 3x grid
                if ($path_options) {
                ?>
                <section class="choose-path-options">
                    <div class="row small-up-1 medium-up-2 large-up-3">
                        <?php
                        foreach ($path_options as $path) {
                            $content = $path['path_description'];
                            if ($content) {
                                echo '<div class="column column-block small-centered">' . $content . '</div>';
                            }
                        }
                        ?>
                    </div>
                </section>
                <?php
                }
                ?>
            </article>
        </div>
    </div>
</main>