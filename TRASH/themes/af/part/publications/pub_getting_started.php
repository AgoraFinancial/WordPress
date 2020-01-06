<?php
// Setup steps/links
$current_step = (isset($_GET['step']) ? $_GET['step'] : 1);
$total_steps = count($getting_started_posts);
$prev_step = ($current_step != 1) ? $current_step - 1 : '';
$next_step = ($current_step < $total_steps) ? $current_step + 1 : '';
$permalink = get_permalink();

if ($next_step) {
    $next_url = get_permalink($getting_started_posts[$next_step]);
    $next_url = add_query_arg(array(
        'type' => 'getting-started',
        'step' => $next_step,
    ), $permalink);
}

if ($prev_step) {
    $prev_url = get_permalink($getting_started_posts[$prev_step]);
    $prev_url = add_query_arg(array(
        'type' => 'getting-started',
        'step' => $prev_step,
    ), $permalink);
}

$content_id = '';
?>
<main class="content-wrapper layout-sidebar-left">
    <div class="row">
        <div class="small-12 medium-4 columns float-right">
            <aside>
                <nav class="side-nav">
                    <ul class="vertical menu">
                        <?php
                        // Create links of all steps
                        foreach ($getting_started_posts as $key => $post_id) {
                            $post_title = get_the_title($post_id);
                            $post_link = add_query_arg(array(
                                'type' => 'getting-started',
                                'step' => $key,
                            ), $permalink);

                            $class = '';
                            if ($key == $current_step) {
                                $class = 'current-step';
                                $content_id = $post_id;
                            }
                        ?>
                        <li class="<?php echo $class; ?>">
                            <a href="<?php echo esc_url($post_link); ?>">
                                Step <?php echo $key; ?>:
                                <?php echo $post_title; ?>
                            </a>
                        </li>
                        <?php
                        }
                        ?>
                    </ul>
                </nav>
            </aside>
        </div>
        <div class="small-12 medium-8 columns">
            <article>
                <h3>Getting Started With <?php the_title(); ?></h3>
                <p class="steps-progress">Step <?php echo $current_step; ?> of <?php echo $total_steps; ?></p>
                <section class="step-content">
                    <?php echo apply_filters('the_content', get_post($content_id)->post_content); ?>
                </section>
                <?php
                // Display previous/next buttons
                if ($prev_step || $next_step) echo '<p class="step-buttons clearfix">';
                if ($prev_step) echo '<a href="'.esc_url($prev_url).'" class="button secondary">Previous Step</a>';
                if ($next_step) echo '<a href="'.esc_url($next_url).'" class="button float-right">Next Step</a>';
                if ($prev_step || $next_step) echo '</p>';
                ?>
            </article>
        </div>
    </div>
</main>