<main class="content-wrapper">
    <div class="row">
        <div class="small-12 medium-10 medium-centered columns">
            <article>
                <h1><?php the_title(); ?></h1>
                <?php echo apply_filters('the_content', $post->post_content); ?>
            </article>
        </div>
    </div>
</main>