<?php
/**
 * Controls data/logic for posts/articles
 */

class AF_Posts extends AF_Theme {
    public function __construct() {
         $this->af_post_hooks();
    }

    // Set up post hooks
    public function af_post_hooks() {
        add_action('wp_ajax_faq_popular_question', array($this, 'af_save_popular_post'));
    }

    // Get all the data you need for a single article
    public function af_single_post_data($post) {
        global $af_theme, $af_publications;

        // Get all ACF fields
        $fields = $this->af_get_acf_fields($post->ID);

        // Get assigned publications
        $article_publications = $fields['publication'];

        // Get the images
        $article_thumb = get_the_post_thumbnail($post, 'article-thumb', array('class' => 'article-thumb'));
        $article_hero = get_the_post_thumbnail($post, 'article-hero', array('class' => 'article-hero'));
        $article_featured_image = get_the_post_thumbnail($post, 'article-featured', array('class' => 'article-hero'));

        // Set category data
        $article_cats = get_the_category($post->ID);
        $cat_count = count($article_cats);
        $cat_index = 0;

        // If is free article (as default), get next category
        if (isset($article_cats[0]->slug) && 'free-articles' == $article_cats[0]->slug && $cat_count > 1) {
            $cat_index = 1;
        }

        // Reset featured image for reports & books
        if (has_category('report', $post) || has_category('books', $post)) {
            $article_featured_image = '';
        }

        // Assign classes based on categories
        $article_classes = $this->af_get_cat_classes($article_cats);

        // Get category id
        $article_category_id = isset($article_cats[$cat_index]->term_id) ? $article_cats[$cat_index]->term_id : '';

        // Get category slug
        $article_category = isset($article_cats[$cat_index]->slug) ? $article_cats[$cat_index]->slug : '';

        // Get category name
        $article_category_name = isset($article_cats[$cat_index]->name) ? $article_cats[$cat_index]->name : '';

        // Get category url
        $article_category_url = isset($article_cats[$cat_index]->term_id) ? get_category_link($article_cats[$cat_index]->term_id) : '';

        // // Set default images if none are set
        if (!$article_thumb) {
            $article_thumb = $this->af_get_article_thumb_placeholder($article_publications, $article_category);
        }

        if (!$article_hero) {
            $article_hero = $this->af_get_article_hero_placeholder($article_publications, $article_category);
        }

        // Get more post meta
        $article_url = get_permalink($post);
        $article_title = get_the_title($post->ID);
        $article_date = get_the_date('F j, Y', $post->ID);
        $article_content = apply_filters('the_content', get_post_field('post_content', $post->ID));
        $embed_codes = isset($fields['embed_codes']) ? $fields['embed_codes'] : '';

        if (is_array($embed_codes) && count($embed_codes)) {
            foreach ($embed_codes as $embed_code) {
                $shortCodeLabel = $embed_code['shortcode_label'];
                $embedContent = $embed_code['embed_code'];
                if ($shortCodeLabel && $embedContent) {
                    $article_content = str_replace('[' . $shortCodeLabel . ']', $embedContent, $article_content);
                }
            }
        }
        $article_excerpt = get_the_excerpt($post->ID);

        // Author tags
        $article_author_meta = get_user_meta($post->post_author);
        $article_author_id = $post->post_author;
        $article_author = $this->af_get_author_name($article_author_meta);
        $article_author_bio = isset($article_author_meta['description'][0]) ? $article_author_meta['description'][0] : false;
        $article_author_bio = force_balance_tags(html_entity_decode(wp_trim_words(htmlentities(wpautop($article_author_bio)), 55, '...')));
        $article_author_url = $this->af_get_author_url($post->post_author, $article_author);
        $article_author_image = $this->af_get_author_image($article_author, $post->post_author);
        $article_twitter = !empty($article_author_meta['twitter'][0]) ? '//twitter.com/' . $article_author_meta['twitter'][0] : false;
        $article_facebook = !empty($article_author_meta['facebook'][0]) ? $article_author_meta['facebook'][0] : false;

        // Additional authors
        $article_coauthors = isset($fields['coauthors']) ? $fields['coauthors'] : '' ;

        // Is lifetime flag
        $article_lifetime = isset($fields['is_lifetime_content'][0]) ? $fields['is_lifetime_content'][0] : '';

        // Is plus flag
        $article_plus = isset($fields['is_plus_content'][0]) ? $fields['is_plus_content'][0] : '';

        // If the post has a download
        $article_download_url = isset($fields['downloadable_pdf_link']) ? $fields['downloadable_pdf_link'] : '';

        // Publish date customization
        $article_date = isset($fields['issue_date_label']) ? $fields['issue_date_label'] : $article_date;
        $article_attribution_site = isset($fields['attribution_site']) ? $fields['attribution_site'] : '';
        $article_attribution_author = isset($fields['attribution_author']) ? $fields['attribution_author'] : '';
        $article_attribution_link = isset($fields['attribution_link']) ? $fields['attribution_link'] : '';

        // Stock recommendations
        $article_stock_recommendations = isset($fields['stock_recommendations']) ? $fields['stock_recommendations'] : '';

        // Get associated pubcodes
        $article_pubcodes = $af_publications->af_get_pubs($post);

        // Get plus content name
        $article_plus_name = '';
        if ($article_plus) {
            $article_plus_name = $this->af_get_plus_name($article_publications);
        }

        // Flag article as new
        $article_new = false;
        $now = time();
        $posted = strtotime($post->post_date);
        $total_seconds = $now - $posted;

        // if seconds is less than 7 days, show new flag
        if ($total_seconds < (60 * 60 * 24 * 7)) {
            $article_new = true;
        }

        // Assemble array with all final data
        $article_data = array(
            'article_id' => $post->ID,
            'article_thumb' => $article_thumb,
            'article_hero' => $article_hero,
            'article_featured_image' => $article_featured_image,
            'article_url' => $article_url,
            'article_author_id' => $article_author_id,
            'article_author' => $article_author,
            'article_author_bio' => $article_author_bio,
            'article_author_url' => $article_author_url,
            'article_author_image' => $article_author_image,
            'article_coauthors' => $article_coauthors,
            'article_twitter' => $article_twitter,
            'article_facebook' => $article_facebook,
            'article_title' => $article_title,
            'article_classes' => $article_classes,
            'article_category_id' => $article_category_id,
            'article_category' => $article_category,
            'article_category_name' => $article_category_name,
            'article_category_url' => $article_category_url,
            'article_date' => $article_date,
            'article_excerpt' => $article_excerpt,
            'article_content' => $article_content,
            'article_lifetime' => $article_lifetime,
            'article_plus' => $article_plus,
            'article_plus_name' => $article_plus_name,
            'article_download_url' => $article_download_url,
            'article_stock_recommendations' => $article_stock_recommendations,
            'article_pubcodes' => $article_pubcodes,
            'article_publications' => $article_publications,
            'article_new' => $article_new,
        );
        return apply_filters('af_article_data', $article_data, $post);
    }

    // Get list of categories to assign as classes
    public function af_get_cat_classes($cats) {
        if (empty($cats)) {
            return false;
        }
        $classes = '';
        foreach ($cats as $cat) {
            $classes .= ' has-cat-' . $cat->category_nicename;
        }
        $classes = ltrim($classes, ' ');
        return $classes;
    }

    // Get article_thumb placeholder
    public function af_get_article_thumb_placeholder($article_publications, $article_category) {
        $article_thumb = '';

        // Get placeholder thumb from publication
        if ($article_publications != '') {
            $image_id = $article_publications[0]->placeholder_thumb;
            if ($image_id == '') {
                return;
            } else {
                $article_thumb = wp_get_attachment_image($image_id, 'article-thumb', '', array('class' => 'article-thumb'));
                return $article_thumb;
            }
        }

        // Get placeholder thumb from category
        if ($article_category != '') {
            $term = get_term_by('slug', $article_category, 'category');
            $free_newsletter_thumb = get_field('free_newsletter_thumb', $term);

            if ($free_newsletter_thumb == '') {
                return;
            } else {
                $free_newsletter_thumb = wp_get_attachment_image($free_newsletter_thumb['id'], 'article-thumb', '', array('class'=>'article-thumb'));
                return $free_newsletter_thumb;
            }
        }
        return;
    }

    // Get article_hero placeholder
    public function af_get_article_hero_placeholder($article_publications, $article_category) {
        $article_hero = '';

        // Get placeholder thumb from publication
        if ($article_publications != '') {
            $image_id = $article_publications[0]->placeholder_image;

            if ($image_id == '') {
                return;
            } else {
                $article_hero = wp_get_attachment_image($image_id, 'article-hero', '', array('class' => 'article-hero'));
                return $article_hero;
            }
        }

        // Get placeholder thumb from category
        if ($article_category != '') {
            $term = get_term_by('slug', $article_category, 'category');
            $free_newsletter_placeholder = get_field('free_newsletter_placeholder', $term);

            if ($free_newsletter_placeholder == '') {
                return;
            } else {
                $free_newsletter_placeholder = wp_get_attachment_image($free_newsletter_placeholder['id'], 'article-hero', '', array('class' => 'article-hero'));
                return $free_newsletter_placeholder;
            }
        }
        return;
    }

    // Get author name
    public function af_get_author_name($article_author_meta) {
        // If no author assigned, return as site
        if (!empty($article_author_meta)) {
            $article_author = $article_author_meta['first_name'][0] . ' ' . $article_author_meta['last_name'][0];
        } else {
            $article_author = get_bloginfo();
        }
        return $article_author;
    }

    // Get author url
    public function af_get_author_url($author, $article_author) {
        // If no author assigned, return as site
        if ($article_author !== get_bloginfo()) {
            $article_author_url = get_author_posts_url($author);
        } else {
            $article_author_url = get_site_url();
        }
        return $article_author_url;
    }

    // Get author avatar image
    public function af_get_author_image($article_author, $author_id = '', $size = 60) {
        $img_path = apply_filters( 'af_author_image_path', 'https://d23xyjuh8cdd4c.cloudfront.net/' );
        $article_author_image = str_replace(' ', '', $article_author);

        // Author image url
        $src = $img_path . $article_author_image . '.jpg';

        // Check if author image and replace w/generic if not
        $header_response = get_headers( $src, 1 );
        if ( strpos( $header_response[0], '404' ) !== false || strpos( $header_response[0], '403' ) !== false ) $src = $img_path . 'author.jpg';
        
        // Image array w/defaults
        $author_image_data = array(
            'src' => $src,
            'id' => $author_id,
            'name' => $article_author,
            'width' => $size,
            'height' => $size,
            'class' => 'avatar avatar-' . $size . ' photo'
        );

        // Apply filter
        $author_image_data = apply_filters( 'af_author_image_data', $author_image_data );

        // Build
        $article_author_image = sprintf(
            '<img src="%s" alt="%s" title="%s" width="%s" height="%s" class="%s">',
            $author_image_data['src'],
            $author_image_data['name'],
            $author_image_data['name'],
            $author_image_data['width'],
            $author_image_data['height'],
            $author_image_data['class']
        );

        return $article_author_image;
    }

    // Get plus_name from pubcodes
    public function af_get_plus_name($pubs) {
        $plus_name = '';
        foreach ($pubs as $pub) {
            $plus_check = get_field('plus_check', $pub->ID);
            if (!$plus_check) {
                continue;
            } else {
                $plus_name = get_field('plus_name', $pub->ID);
            }
        }
        return $plus_name;
    }

    // Get post excerpt whilst passing in variables
    public function af_get_post_excerpt($single_post, $imagesize = '', $readmore = 'Read More') {
        global $af_templates;

        // Set variables to pass into part
        set_query_var('single_post', $single_post);
        set_query_var('imagesize', $imagesize);
        set_query_var('readmore', $readmore);
        $af_templates->af_post_excerpt();
    }

    public function af_post_content() {
        echo $this->af_get_post_content();
    }

    // Get post content based on permissions
    public function af_get_post_content() {
        global $post, $af_theme, $af_auth, $af_publications, $af_templates, $pages;

        if (function_exists('get_field')) {
            $publication = get_field('publication', $post->ID);
        }
        $content = apply_filters('the_content', $post->post_content);
        $pubcodes = $af_publications->af_get_pubs($post);
        $is_lifetime = $af_auth->is_post_lifetime($post->ID);
        $is_plus = $af_auth->is_post_plus($post->ID);
        $is_free = false;

        // Check free articles against global setting
        if (defined('FREE_ARTICLES_ONLY') && FREE_ARTICLES_ONLY === true) {
            $is_free = true;
        } elseif ( has_category($af_theme->af_get_free_article_cats()) && empty($pubcodes) ) {
            $is_free = true;
        }

        // Show excerpt and paygate if not free or subscribed
        if (!$af_auth->has_pub_access($pubcodes, $is_lifetime, $is_plus) && !$is_free) {
            $content = has_excerpt($post->ID) ? wpautop(get_the_excerpt($post->ID)) : ($pages !== null ? wpautop(strip_shortcodes(wp_trim_words(get_the_content(), 40, '...'))) : '');
            $content .= $af_templates->af_paygate_post();
            echo $content;
        } else { // Show full content

            // If is free content - add OpenX incontent ads
            if ($is_free) {

                // Check for manual placement of openx
                $has_placeholder = strpos( $content, '%%OPENX_AD%%' );

                // Break content and rebuild and add openx at first instance of placeholder
                if ($has_placeholder) {
                    $content = str_replace( '<p>%%OPENX_AD%%</p>', '%%OPENX_AD%%', $content );
                    $parts = explode( '%%OPENX_AD%%', $content );
                    $count = count( $parts );
                    $content = '';
                    for ( $i = 0; $i <= $count; $i++ ) {
                        $content .= $parts[$i];
                        $content .= $i === 0 ? $af_templates->af_openx_in_content() : '' ;
                    }
                }

                // Default ad openx in the middle
                else {
                    $paragraphs = explode('</p>', $content);
                    if (count($paragraphs) > 5) {
                        $idx = (count($paragraphs) % 2) ? (count($paragraphs) - 1) / 2 : count($paragraphs) / 2;
                        foreach ($paragraphs as $cur_idx => $content) {
                            $paragraphs[$cur_idx] .= '</p>';
                            if ($cur_idx == $idx) {
                                $paragraphs[$cur_idx] .= $af_templates->af_openx_in_content();
                            }
                        }
                        $content = implode('', $paragraphs);
                    }
                }
            }

            // Append post actions if logged in
            if (is_user_logged_in() && !$is_free) {
                $content = $af_templates->af_post_actions() . $content;
            }
            return $content;
        }
    }

    // Get list of valid category IDs for publications
    public function af_get_active_cats() {
        $cat_ids = '';

        // Get list of included categories
        $slugs = $this->af_whats_new_cats();
        $include = array();
        foreach ($slugs as $slug) {
            $include[] = get_category_by_slug($slug)->term_id;
        }
        $args = array('include' => $include);
        $cats = get_terms('category', $args);

        if ($cats) {
            $cat_ids = array();
            foreach ($cats as $cat) {
                $cat_ids[] = $cat->term_id;
            }
        }
        return $cat_ids;
    }

    // Create array of desired categories on the what's new pages
    public function af_whats_new_cats($slugs = array()) {
        $slugs = array('alert', 'flash-alert', 'issue', 'report', 'update', 'video');
        return $slugs;
    }

    // Check if is frontend post
    public function af_is_frontend_post() {
        global $post, $af_publications;
        $pubs = $af_publications->af_get_pubs($post);
        $is_frontend = false;

        foreach ($pubs as $pub) {
            $pub_id = $af_publications->af_get_pub_ID($pub);
            $subscription_type = get_field('subscription_type', $pub_id);
            if ($subscription_type == 'Frontend') {
                $is_frontend = true;
            }
        }
        return $is_frontend;
    }

    // Check if is backend post
    public function af_is_backend_post() {
        global $post, $af_publications;
        $pubs = $af_publications->af_get_pubs($post);
        $is_backend = false;

        foreach ($pubs as $pub) {
            $pub_id = $af_publications->af_get_pub_ID($pub);
            $subscription_type = get_field('subscription_type', $pub_id);
            if ($subscription_type == 'Backend') {
                $is_backend = true;
            }
        }
        return $is_backend;
    }

    // Get popular faqs
    public function af_get_popular_posts() {
        global $wpdb;
        $ar_articles = array();
        $prev_month = date('Y-m-d', strtotime('-1 month'));
        $query = "SELECT p.ID, count(pp.post_id) AS myCount FROM wp_posts p LEFT JOIN wp_popular_posts pp ON p.ID=pp.post_id WHERE pp.view_date > '" . $prev_month . "' AND p.post_status='publish' AND p.post_type='faq' GROUP BY p.ID ORDER BY myCount DESC LIMIT 10";
        $ar_results = $wpdb->get_results($query, 'OBJECT');

        if (!$ar_results) {
            return $ar_articles;
        }
        foreach ($ar_results as $obj_result) {
            if ($obj_result->ID) {
                $ar_articles[] = $obj_result->ID;
            }
        }
        return $ar_articles;
    }

    // Save popular faqs
    public function af_save_popular_post() {
        global $wpdb;
        $help_slug = isset($_POST['help_slug']) ? $_POST['help_slug'] : '';
        $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : '';
        $today = date('Y-m-d');
        $wpdb->insert(
            'wp_popular_posts',
            array(
                'post_id' => $post_id,
                'view_date' => $today
            ),
            array(
                '%d',
                '%s'
            )
        );
    }
}