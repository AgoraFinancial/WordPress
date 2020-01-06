<main class="content-wrapper authors-section">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <article>
                <?php echo apply_filters('the_content', $post->post_content); ?>
            </article>
        </div>
    </div>
    <div class="row">
        <div class="small-12 columns">
            <section class="author-list">
                <div class="row small-up-1 medium-up-2 large-up-3">
                    <?php
                    global $af_posts;
                    $template_directory_uri = PARENT_PATH_URI;
                    $authors = new WP_User_Query(
                        array(
                            'role'                => 'author',
                            'has_published_posts' =>  array('post'),
                            'number'              => -1,
                            'paged'               => false,
                        )
                    );

                    foreach ($authors->results as $author) {
                        $author_meta = get_user_meta($author->ID);
                        $hide_from_authors_page = get_field('hide_from_authors_page', 'user_' . $author->ID);

                        if ($hide_from_authors_page) {
                            continue;
                        }

                        $author_fullname = $author_meta['first_name'][0] . ' ' . $author_meta['last_name'][0];
                        $author_url = get_author_posts_url($author->ID);
                        $author_bio = force_balance_tags(html_entity_decode(wp_trim_words(htmlentities(wpautop($author_meta['description'][0])), 55, '...')));
                        $author_image = str_replace(' ', '', $author_fullname);
                        $author_image = $af_posts->af_get_author_image( $author_fullname, $author->ID, 100 );
                    ?>
                    <div class="column column-block">
                        <div class="author-card">
                            <p class="centered">
                                <a class="author-logo-link" href="<?php echo $author_url; ?>">
                                    <?php echo $author_image; ?>
                                </a>
                            </p>
                            <h3 class="h4 centered">
                                <a href="<?php echo $author_url; ?>">
                                    <?php echo $author_fullname; ?>
                                </a>
                            </h3>
                            <p><?php echo $author_bio; ?></p>
                            <p class="author-actions">
                                <a class="button" href="<?php echo $author_url; ?>">View Full Bio</a>
                            </p>
                        </div>
                    </div>
                    <?php
                    }
                    ?>
                </div>
            </section>
        </div>
    </div>
</main>