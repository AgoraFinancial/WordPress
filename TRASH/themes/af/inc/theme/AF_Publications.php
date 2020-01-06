<?php
/**
 * Controls data/logic for publications
 */

class AF_Publications extends AF_Theme {
    public function __construct() {
        $this->af_pub_archive_setup();
    }

    // Modify query for publications archive
    public function af_pub_archive_setup() {
        add_action('pre_get_posts', array($this, 'af_pub_archive_limit'));
    }

    // Always show all publications for pub archive page
    public function af_pub_archive_limit($query) {
        if (!is_admin() && $query->is_post_type_archive('publications') && $query->is_main_query()) {
                $query->set('posts_per_page', -1);
                $query->set('orderby', 'menu_order');
                $query->set('order', 'ASC');
        }
    }

    // Get all the data you need for a publication
    public function af_get_pub_data($pub_id) {

        // Check if already cached
        $transient_id = 'af_transient_pub_' . $pub_id;
        $transient_data = get_transient($transient_id);
        if($transient_data) return apply_filters('af_publication_data', $transient_data, $pub_id);

        // Get all ACF fields
        $fields = $this->af_get_acf_fields($pub_id);

        // Create variables from ACF
        $pub_code = $fields['pubcode'];
        $publication_editor = isset($fields['publication_editor']) ? $fields['publication_editor'] : '';
        $subscription_type = isset($fields['subscription_type']) ? $fields['subscription_type'] : '';
        $subscribe_url = isset($fields['subscribe_url']) ? $fields['subscribe_url'] : '';
        $renewal_url = isset($fields['renewal_url']) ? $fields['renewal_url'] : '';
        $lifetime_url = isset($fields['lifetime_url']) ? $fields['lifetime_url'] : '';
        $categories = isset($fields['categories']) ? $fields['categories'] : '';
        $pub_logo = isset($fields['pub_logo']) ? $fields['pub_logo'] : '';
        $header_image = isset($fields['header_image']) ? $fields['header_image'] : '';
        $short_description = isset($fields['short_description']) ? $fields['short_description'] : '';
        $paygate_content = isset($fields['paygate_content']) ? $fields['paygate_content'] : '';
        $plus_check = isset($fields['plus_check']) ? $fields['plus_check'] : '';
        $plus_name = isset($fields['plus_name']) ? $fields['plus_name'] : '';
        $plus_portfolio_code = isset($fields['plus_portfolio_code']) ? $fields['plus_portfolio_code'] : '';
        $plus_paygate_content = isset($fields['plus_paygate_content']) ? $fields['plus_paygate_content'] : '';
        $portfolio_id = isset($fields['custom_portfolio_id']) && !empty($fields['custom_portfolio_id']) ? $fields['custom_portfolio_id'] : $pub_code;
        $portfolio_tradegroups = isset($fields['group_names']) ? $fields['group_names'] : '';
        $portfolio_columns = isset($fields['portfolio_columns']) ? $fields['portfolio_columns'] : '';
        $pub_folded = isset($fields['pub_folded']) && $fields['pub_folded'] == 1 ? $fields['pub_folded'] : 0;
        $hide_from_public = isset($fields['hide_from_public']) && $fields['hide_from_public'] == 1 ? $fields['hide_from_public'] : 0;
        $remove_all_access = isset($fields['remove_all_access']) && $fields['remove_all_access'] == 1 ? $fields['remove_all_access'] : 0;

        // Editor data
        if ('' !== $publication_editor) {
            $editor_id = $publication_editor['ID'];
            $editor_name = $publication_editor['user_firstname'] . ' ' . $publication_editor['user_lastname'];
            $editor_url = get_author_posts_url($publication_editor['ID']);
            $editor_image = $this->af_get_editor_image($editor_name);
            $editor_meta = get_user_meta($publication_editor['ID']);
            $editor_bio = isset($editor_meta['description'][0]) ? $editor_meta['description'][0] : false;
            $editor_bio = force_balance_tags(html_entity_decode(wp_trim_words(htmlentities(wpautop($editor_bio)), 55, '...')));
            $editor_twitter = !empty($editor_meta['twitter'][0]) ? '//twitter.com/' . $editor_meta['twitter'][0] : false;
            $editor_facebook = !empty($editor_meta['facebook'][0]) ? $editor_meta['facebook'][0] : false;
        } else {
            $editor_id = $editor_name = $editor_url = $editor_image = $editor_meta = $editor_bio = $editor_twitter = $editor_facebook = '';
        }

        // Return image ACF as responsive <img> strings
        if ('' !== $pub_logo) {
            $pub_logo_full = wp_get_attachment_image($pub_logo['id'], 'full', '', array('class' => 'pub-logo-full'));
            $pub_logo = wp_get_attachment_image($pub_logo['id'], 'medium_large', '', array('class' => 'pub-logo'));
        } else {
            $pub_logo_full = $pub_logo = '';
        }
        if ('' !== $header_image) {
            $header_image = wp_get_attachment_image($header_image['id'], 'medium_large', '', array('class' => 'pub-featured-image'));
        }

        // Generic post data
        $pub_title = get_the_title($pub_id);
        $pub_object = get_post($pub_id);
        $pub_description = apply_filters('the_content', $pub_object->post_content);
        $pub_excerpt = (has_excerpt($pub_object->ID)) ? get_the_excerpt($pub_object->ID) : wp_trim_words($pub_description, 55, '...');
        $pub_url = get_the_permalink($pub_id);

        // Portfolio Array
        $columns_array = '';
        $hide_open_array = array();
        $hide_closed_array = array();
        if ($portfolio_columns) {
            $columns_array = array();
            foreach ($portfolio_columns as $column) {
                $columns_array[$column['column_id']] = $column['column_label'];
                if ($column['hide_open']) {
                    $hide_open_array[] = $column['column_id'];
                }
                if ($column['hide_closed']) {
                    $hide_closed_array[] = $column['column_id'];
                }
            }
        }
        $portfolio_columns = $columns_array;
        $portfolio_hide_open = $hide_open_array;
        $portfolio_hide_closed = $hide_closed_array;

        // Create array from data
        $pub_data = array(
            'pub_id' => $pub_id,
            'pubcode' => $pub_code,
            'editor_id' => $editor_id,
            'editor_name' => $editor_name,
            'editor_url' => $editor_url,
            'editor_image' => $editor_image,
            'editor_bio' => $editor_bio,
            'editor_twitter' => $editor_twitter,
            'editor_facebook' => $editor_facebook,
            'subscription_type' => $subscription_type,
            'subscribe_url' => $subscribe_url,
            'renewal_url' => $renewal_url,
            'lifetime_url' => $lifetime_url,
            'categories' => $categories,
            'pub_logo_full' => $pub_logo_full,
            'pub_logo' => $pub_logo,
            'header_image' => $header_image,
            'short_description' => $short_description,
            'paygate_content' => $paygate_content,
            'plus_check' => $plus_check,
            'plus_name' => $plus_name,
            'plus_portfolio_code' => $plus_portfolio_code,
            'plus_paygate_content' => $plus_paygate_content,
            'pub_title' => $pub_title,
            'pub_description' => $pub_description,
            'pub_excerpt' => $pub_excerpt,
            'pub_url' => $pub_url,
            'portfolio_id' => $portfolio_id,
            'portfolio_tradegroups' => $portfolio_tradegroups,
            'portfolio_columns' => $portfolio_columns,
            'portfolio_hide_open' => $portfolio_hide_open,
            'portfolio_hide_closed' => $portfolio_hide_closed,
            'pub_folded' => $pub_folded,
            'hide_from_public' => $hide_from_public,
            'remove_all_access' => $remove_all_access,
        );
        // Set transient
        set_transient( $transient_id, $pub_data, 60 * 60 * 1 );
        return apply_filters('af_publication_data', $pub_data, $pub_id);
    }

    // Get edtior avatar image
    public function af_get_editor_image($editor_name, $img_size = 60) {
        $img_path = apply_filters( 'af_editor_image_path', 'https://d23xyjuh8cdd4c.cloudfront.net/' );
        $editor_image = str_replace(' ', '', $editor_name);

        // Editor image url
        $src = $img_path . $editor_image . '.jpg';

        // Check if editor image and replace w/generic if not
        $header_response = get_headers( $src, 1 );
        if ( strpos( $header_response[0], '404' ) !== false || strpos( $header_response[0], '403' ) !== false ) $src = $img_path . 'author.jpg';

        // Build editor image
        $editor_image = '<img src="' . $src . '" alt="' . $editor_name . '" title="' . $editor_name . '" height="' . $img_size . '" width="' . $img_size . '" class="avatar avatar-' . $img_size . ' photo">';
        $editor_image = apply_filters('af_editor_image', $editor_image, $src, $editor_name, $img_size);

        return $editor_image;
    }

    // Get list of all active pub IDs
    public function af_get_active_pub_ids() {
        
        // Check if already cached
        $transient_id = 'af_transient_active_pub_ids';
        $transient_data = get_transient($transient_id);
        if($transient_data) return $transient_data;

        $pub_ids = '';
        $args = array(
            'post_type' => 'publications',
            'posts_per_page' => -1,
            'order' => 'ASC',
            'orderby' => 'menu_order',
            'meta_query' => array(
                array(
                    'key' => 'hide_from_public',
                    'compare' => '!=',
                    'value' => '1'
                )
            )
        );
        $pubs = get_posts($args);

        if ($pubs) {
            $pub_ids = array();
            foreach ($pubs as $pub) {
                $pub_ids[] = $pub->ID;
            }
        }

        // Set transient
        set_transient( $transient_id, $pub_ids, 60 * 60 * 24 * 7 );
        return $pub_ids;
    }

    // Conditional check if website has any hidden publications
    public function af_has_hidden_pubs() {
        $active_pubcodes = $this->af_get_active_pubcodes();
        $visible_pub_count = (is_array($active_pubcodes) || $active_pubcodes instanceof Countable) && count($active_pubcodes) ? $active_pubcodes : '';

        // Get post count.
        $published_pub_object = wp_count_posts('publications');

        // Get all published and draft.
        $published_count =  $published_pub_object->publish;
        $draft_count =  $published_pub_object->draft;

        // Get active pubs from publish less drafts.
        $published_pub_count =  $published_count - $draft_count;

        if ($visible_pub_count == $published_pub_count) {
            return;
        }
        return true;
    }

    // Get list of all active pubcodes
    public function af_get_active_pubcodes() {

         // Check if already cached
        $transient_id = 'af_transient_active_pubcodes';
        $transient_data = get_transient($transient_id);
        if($transient_data) return $transient_data;

        $pub_codes = '';
        $args = array(
            'post_type' => 'publications',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'hide_from_public',
                    'compare' => '!=',
                    'value' => '1'
                )
            )
        );
        $pubs = get_posts($args);

        if ($pubs) {
            $pub_codes = array();
            foreach ($pubs as $pub) {
                $pub_codes[] = get_field('pubcode', $pub->ID);
            }
        }

        // Set transient
        set_transient( $transient_id, $pub_codes, 60 * 60 * 24 * 7 );
        return $pub_codes;
    }

    // Get list of all user accessible pubcodes
    public function af_get_user_pubcodes() {
        global $af_auth;
        $pub_codes = '';
        $all_user_pubs = $af_auth->get_current_user_active_pubs();
        $local_user_pubs = $this->af_get_active_pubcodes();

        // For admins or editors, give access to all pubs
        if (current_user_can('administrator') || current_user_can('editor')) {
            $pub_codes = $local_user_pubs;
        } else {
            $pub_codes = array_intersect($all_user_pubs, $local_user_pubs);
        }
        return $pub_codes;
    }

    // Get list of all user accessible pub ids
    public function af_get_user_pub_ids() {
        $pub_ids = array();
        $pub_codes = $this->af_get_user_pubcodes();
        foreach ($pub_codes as $pub_code) {
            $pub_ids[] = $this->af_get_pub_ID($pub_code);
        }
        return $pub_ids;
    }

    // Get all publications associated with post and set to array
    public function af_get_pubs($post) {
        $pubs = array();
        if (function_exists('get_field')) {
            $publication = get_field('publication', $post->ID);
            if ($publication) {
                foreach ($publication as $pub) {
                    $pubs[] = get_field('pubcode', $pub->ID);
                }
            }
        }
        return $pubs;
    }

    // Get publication post ID by pubcode
    public function af_get_pub_ID($pub_code) {
        if ($pub_code == '') {
            return;
        }
        $pub_id = '';
        $args = array(
            'post_type' => 'publications',
            'meta_key' => 'pubcode',
            'meta_value' => $pub_code
        );
        $pub_query = new WP_Query($args);
        while ($pub_query->have_posts()) {
            $pub_query->the_post();
            $pub_id = get_the_ID();
        }
        wp_reset_postdata();
        return $pub_id;
    }

    // Get valid pubcodes that have access to a folded/temp pubcodes
    public function af_get_temp_access_pubs($tmp_pubcode) {
        if ($tmp_pubcode == '') {
            return;
        }
        $pub_codes = array();

        // Get temp pubcode id
        $tmp_id = $this->af_get_pub_ID($tmp_pubcode);

        // Check if pub has inherited access
        $arr_access = get_field('inherit_access', $tmp_id);

        // Get inherited access pubcodes
        if (is_array($arr_access) && !empty($arr_access)) {
            foreach ($arr_access as $access_id) {
                $pub_codes[] = get_field('pubcode', $access_id);
            }
        }
        return $pub_codes;
    }

    // Get publication post ID by meta key/value
    public function af_get_pub_ID_by_meta($key, $value) {
        if (($key || $value) == '') {
            return;
        }
        $pub_id = '';
        $args = array(
            'post_type' => 'publications',
            'meta_key' => $key,
            'meta_value' => $value
        );
        $pub_query = new WP_Query($args);

        while ($pub_query->have_posts()) {
            $pub_query->the_post();
            $pub_id = get_the_ID();
        }
        wp_reset_postdata();

        return $pub_id;

    }

    // Get any valid pub id from post
    public function af_get_valid_pub_ID() {
        global $post, $af_auth;
        $pub_id = '';
        $pubs = $this->af_get_pubs($post);

        // Get id of accessible pub
        foreach ($pubs as $pub_code) {
            if ($af_auth->has_pub_access($pub_code)) {
                $pub_id = $this->af_get_pub_ID($pub_code);
                return $pub_id;
            }
        }
        return $pub_id;
    }

    // Echo af_get_pub_nav()
    public function af_pub_nav($pub_id) {
        echo $this->af_get_pub_nav($pub_id);
    }

    // Get publication category navigation
    public function af_get_pub_nav($pub_id) {
        global $post;

        // Get category data & type
        $category_data = $this->af_get_pub_cat_data($pub_id);
        $type = $this->af_get_pub_type($category_data);
        $has_faqs = function_exists('get_field') ? get_field('grouped_faqs_check', $pub_id) : false;
        $initial = true;
        $html = '';

        if (is_array($category_data) && count($category_data)) {
            foreach ($category_data as $category) {
                $is_active = '';
                $category_slug = isset($category['category_slug']) && $category['category_slug'] != '' ? $category['category_slug'] : $category['category']->slug;
                // echo $category_slug
                $link_url = add_query_arg('type', $category_slug, get_permalink($pub_id));
                $cat_name = $category['category_label'] != '' ? $category['category_label'] : $category['category']->name;

                // Make an exception for the getting-started and help categories and float them right
                if ($category_slug == 'getting-started' || $category_slug == 'help') {
                    $is_active .= ' callout-nav float-right';
                }

                // If is single post, set active to category
                if (is_singular('post')) {
                    $post_category = get_the_category($post->ID);
                    $post_category = $post_category[0]->slug;
                    if ($category_slug == $post_category) {
                        $is_active .= ' is-active';
                        $initial = false;
                    }
                } else { // Based on query string
                    if ($category_slug == $type) {
                        $is_active .= ' is-active';
                        $initial = false;
                    }
                }
                $html .= '<li class="tabs-title' . $is_active . '"><a href="' . esc_url($link_url) . '">' . $cat_name . '</a></li>';
            }
        }
        return $html;
    }

    // Get publication category data
    public function af_get_pub_cat_data($pub_id) {
        $pub_data = $this->af_get_pub_data($pub_id);
        $category_data = $pub_data['categories'];
        return $category_data;
    }

    // Get publication page type
    public function af_get_pub_type($category_data) {
        $type = isset($_GET['type']) ? $_GET['type'] : (is_array($category_data) ? $category_data[0]['category']->slug : '');
        return $type;
    }

    public function af_pub_content() {
        echo $this->af_get_pub_content();
    }

    // Get post content based on permissions
    public function af_get_pub_content() {
        global $post, $af_auth, $af_templates, $content;
        $pub_data = $this->af_get_pub_data($post->ID);
        $pub_folded = $pub_data['pub_folded'];
        $hide_open = $hide_closed = '';

        // Pass variable into part
        set_query_var('pub_data', $pub_data);

        // If user doesn't have access, show pub description
        if (!$af_auth->has_pub_access($pub_data['pubcode'])) {
            $content = apply_filters('the_content', $post->post_content);
            $content .= apply_filters('af_append_editor_bio', $af_templates->af_editor_bio());

            // Pass variable into part
            set_query_var('content', $content);
            set_query_var('pub_folded', $pub_folded);
            $af_templates->af_single_publications();
            return;
        }

        // Get data and set flags
        $category_data = $this->af_get_pub_cat_data($pub_data['pub_id']);
        $type = $this->af_get_pub_type($category_data);
        $has_faqs = function_exists('get_field') ? get_field('grouped_faqs_check') : false;
        $has_getting_started = function_exists('get_field') ? get_field('getting_started_check') : false;
        $is_archive = isset($_GET['archive']) && is_numeric($_GET['archive']) && strlen($_GET['archive']) == 6 ? $_GET['archive'] : false;
        $custom_content = apply_filters( 'af_filter_pub_content', false, $type );

        // Check for static content
        if (is_array($category_data) && count($category_data)) {
            foreach ($category_data as $category) {
                $slug = !empty($category['category_slug']) ? $category['category_slug'] : $category['category']->slug;
                $static_override = $category['static_override'];
                $static_content = $category['static_content'];
                $static_content_html = $category['static_content_html'];
                $static_content_width = $category['static_content_size'];

                // Skip if not flagged for static
                if (!$static_override) {
                    continue;
                }

                // Check slug if has staic
                if ($slug == $type && $static_override) {
                    $content = $static_content;
                    $content_html = $static_content_html;
                    $content_size = isset($static_content_width) ? $static_content_width : 10;

                    // Pass variable into part
                    set_query_var('content', $content);
                    set_query_var('content_html', $content_html);
                    set_query_var('content_size', $content_size);
                    $af_templates->af_pub_static();
                    return;
                } else {
                    continue;
                }
            }
        }

        // Add filter for custom tab hook
        // Must return true to be considered
        $custom_tab = apply_filters( 'af_publication_custom_tab', $type, $pub_data );
        if($custom_tab === true) return;

        // If category is 'getting-started'
        if ($type == 'getting-started' && $has_getting_started) {
            $getting_started_posts = function_exists('get_field') ? get_field('getting_started_posts') : '';

            // Start array at [1]
            $getting_started_posts = array_combine(range(1, count($getting_started_posts)), $getting_started_posts);

            // Pass variable into part
            set_query_var('getting_started_posts', $getting_started_posts);
            $af_templates->af_pub_getting_started();
            return;
        }

        // If category is 'help'
        if ($type == 'help' && $has_faqs) {
            $grouped_faqs = function_exists('get_field') ? get_field('grouped_faqs') : '';

            // Pass variable into part
            set_query_var('grouped_faqs', $grouped_faqs);
            $af_templates->af_pub_faqs();
            return;
        }

        // If category is 'portfolio'
        if ($type == 'portfolio') {

            // Symbol,Name,Comments,Entry Date,Entry Price,Current Price,Percent Gain,Window Close,Stop Loss
            $ar_columns = array(
                'symbol' => 'Symbol',
                'name' => 'Name',
                'comments' => 'Comments',
                'entryDate' => 'Entry Date',
                'entryPrice' => 'Entry Price',
                'closeDate' => 'Exit Date',
                'lastPrice' => 'Current Price',
                'dividends' => 'Dividends',
                'percentGain' => 'Percent Gain'
            );

            // Use custom columns set in admin
            if ($pub_data['portfolio_columns']) {
                $ar_columns = $pub_data['portfolio_columns'];
            }
            if ($pub_data['portfolio_hide_open']) {
                $hide_open = $pub_data['portfolio_hide_open'];
            }
            if ($pub_data['portfolio_hide_closed']) {
                $hide_closed = $pub_data['portfolio_hide_closed'];
            }

            $portfolioResponse = $this->af_get_pub_portfolio($ar_columns, $pub_data['portfolio_id']);
            set_query_var('portfolio_columns', $ar_columns);
            set_query_var('hide_open', $hide_open);
            set_query_var('hide_closed', $hide_closed);
            set_query_var('portfolio_response', $portfolioResponse);
            $af_templates->af_pub_portfolio();
            return;
        }

        // If is archive
        if ($is_archive) {
            // echo $archive;
            $month = (int)substr($is_archive, 0, 2);
            $year = (int)substr($is_archive, 2);

            // Pass variable into part
            set_query_var('type', $type);
            set_query_var('year', $year);
            set_query_var('month', $month);
            $af_templates->af_pub_archive();
            return;
        }

        // If custom content interrupt is defined add hook
        if ($custom_content) {
            do_action( 'af_hook_pub_content', $type );
            return;
        }

        // Rest of categories. Pass variable into part.
        set_query_var('type', $type);
        $af_templates->af_pub_articles_list();
        return;

    }


    // Get portfolio for pub
    public function af_get_pub_portfolio($ar_columns, $pub_code) {

        if (empty($ar_columns)) return 'Missing columns';
        if (empty($pub_code)) return 'Missing pubcode';

        $ar_portfolio = array();

        $transient_id = 'portfolio_response_'.strtolower($pub_code);

        // For dev, if URL contains &ref=1 it will delete the transient
        if (isset($_GET['ref']) && $_GET['ref'] == 1) delete_transient($transient_id);

        // Check if transient of response exisits, otherwise, go get it
        $transient = get_transient($transient_id);
        if ($transient) {
            $ar_portfolio = $transient;
        } else {
            $ch = curl_init("https://publishers.tradesmith.com/api/ApiPortfolio/GetAllPortfolios?apiKey=vOc5aQlqE0KaaDwSwGZ4aw");
            curl_setopt_array($ch, array(
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_RETURNTRANSFER => true
            ));
            $response = curl_exec($ch);
            $ar_responses = json_decode($response);
            $id = 0;
            foreach ($ar_responses as $arResponse) {
                if ($pub_code == $arResponse->Name) {
                    $id = $arResponse->Id;
                }
            }
            if (!$id) {
                return '';
            }

            $ch = curl_init("https://publishers.tradesmith.com/api/ApiPosition/GetAllByPortfolioId?apiKey=vOc5aQlqE0KaaDwSwGZ4aw&portfolioId=$id");
            curl_setopt_array($ch, array(
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_RETURNTRANSFER => true
            ));
            $response = curl_exec($ch);
            $ar_responses = json_decode($response);
            $ar_portfolio = array();

            // Loop throught API response and build the array we need for building the table
            foreach ($ar_responses as $objPortfolio){
                /**
                 * This only skips unpublished that do not have tradegroup
                 * names to accommodate failure on tradesmiths side. We will
                 * ignore most, but still capture the few that have names but
                 * are unpublished and removed later
                 */
                if (!$objPortfolio->PositionSetting->Published && !isset($objPortfolio->TradeGroup->Name) && $objPortfolio->TradeGroup->Name == '') {
                    continue;
                }

                if (isset($objPortfolio->TradeGroup->{'$id'})) {
                    $tradeGroupId = $objPortfolio->TradeGroup->{'$id'};
                } elseif (isset($objPortfolio->TradeGroup->{'$ref'})) {
                    $tradeGroupId = $objPortfolio->TradeGroup->{'$ref'};
                } else {
                    $tradeGroupId = '';
                }

                $tradeGroupName = isset($objPortfolio->TradeGroup->Name) ? $objPortfolio->TradeGroup->Name : '';
                $entryPrice = $objPortfolio->OpenPrice ? money_format('%.2n', $objPortfolio->OpenPrice) : '';
                $percentGain = isset($objPortfolio->Gain) ? round($objPortfolio->Gain, 2) * 100 . '%' : '';
                $percentGain = $this->af_get_gain_direction($percentGain);
                $status = !empty($objPortfolio->IsOpened) ? 'openContent' : 'closeContent';
                $trailingStopPercentage = isset($objPortfolio->Subtrades[0]->SubtradeSetting->TrailingStop) ? '%' . $objPortfolio->Subtrades[0]->SubtradeSetting->TrailingStop : '';
                $isClosed = empty($objPortfolio->IsOpened) ? 'Closed' : '';
                $lastPrice = isset($objPortfolio->CurrentClosePrice) ? money_format('%.2n', $objPortfolio->CurrentClosePrice) : '';
                $dividends = isset($objPortfolio->Dividends) ? money_format('%.2n', $objPortfolio->Dividends) : '';
                $closeDate = empty($objPortfolio->IsOpened) ? date("m/d/Y",strtotime($objPortfolio->CloseDate)) : 'Open';
                $lastDate = isset($row->CloseDate) ? date("m/d/Y", strtotime($row->CloseDate)) : '';
                $exDividendDate = $this->af_get_ExDividendDate($objPortfolio);
                $isOption = $this->af_get_isOption($objPortfolio);
                $stopLoss = $this->af_get_stopLoss($objPortfolio);
                $stopPrice = $this->af_get_stopPrice($objPortfolio);
                $taxStatus = $objPortfolio->TaxStatus == 1 ? 'Yes' : 'No' ;
                $isCanadianPensionPlan = $objPortfolio->IsCanadianPensionPlan ? 'Yes' : 'No';

                // Carry over published status for empty trade groups
                $published = isset($objPortfolio->PositionSetting->Published) && $objPortfolio->PositionSetting->Published ? 1 : 0 ;

                $ar_portfolio[] = array(
                    'tradeGroupId' => $tradeGroupId,
                    'tradeGroupName' => $tradeGroupName,
                    'status' => $status,
                    'published' => $objPortfolio->PositionSetting->Published,
                    'columns' => array(
                        'action' => $objPortfolio->PositionSetting->Text1,
                        'comments' => $objPortfolio->PositionSetting->Text2,
                        'text3' => $objPortfolio->PositionSetting->Text3,
                        'text4' => $objPortfolio->PositionSetting->Text4,
                        'text5' => $objPortfolio->PositionSetting->Text5,
                        'TaxStatus' => $taxStatus,
                        'IsCanadianPensionPlan' => $isCanadianPensionPlan,
                        'ExDividendDate' => $exDividendDate,
                        'dividends' => $dividends,
                        'entryDate' => date("m/d/Y", strtotime($objPortfolio->OpenDate)),
                        'entryPrice' => $this->af_set_dollar($entryPrice),
                        'closeDate' => $closeDate,
                        'isClosed' => $isClosed,
                        'isOption' => $isOption,
                        'lastPrice' => $this->af_set_dollar($lastPrice),
                        'lastDate' => $lastDate,
                        'name' => $objPortfolio->Name,
                        'percentGain' => $percentGain,
                        'symbol' => $objPortfolio->Symbol,
                        'stopLoss' => $stopLoss,
                        'trailingStopPercentage' => $trailingStopPercentage,
                        'stopPrice' => $stopPrice,
                        'cashAllocation' => $this->af_set_dollar($objPortfolio->CashAllocation),
                        'dollarGain' => $this->af_set_dollar($objPortfolio->DollarGain),
                        'recomendation' => $this->af_set_recomendation($objPortfolio->PositionSetting->Recommend),
                    )
                );
            }

            array_multisort(
                array_column($ar_portfolio, 'tradeGroupId'), SORT_ASC,
                array_column($ar_portfolio, 'tradeGroupName'), SORT_DESC,
                $ar_portfolio
            );
            set_transient($transient_id, $ar_portfolio, 60 * 60 * 1);
        }
        return $ar_portfolio;
    }

    // Get dollar format for portfolio
    public function af_set_dollar($data) {
        if (preg_match("/[a-z]/i", $data) || $data == 0) {
            return $data;
        }
        if (!strpos($data, '$')) {
            $data = '$'.$data;
        } else {
            return $data;
        }
        return $data;
    }

    // Get Recomendation from ID
    public function af_set_recomendation($data) {
    $arLookup = array("10"=>"Buy","20"=>"Buy Cover","30"=>"Buy To Close","35"=>"Buy To Open","40"=>"Hold","50"=>"Hold Short","60"=>"Sell","70"=>"Sell Short","80"=>"Sell To Open","90"=>"Reduce","100"=>"Sell Put");
    $val = isset($data) ? $data : '';
    $val = isset($arLookup["$val"]) ? $arLookup["$val"] : '';

        return $val;
    }

    // Wrap percentage in span to denote pos/neg
    public function af_get_gain_direction($val) {
        $class = '';
        if (substr($val, 0, 1) == '-') {
            $class = 'alert';
        } else {
            $class = 'success';
        }
        return '<span class="percent-gain-direction ' . $class . '">' . $val . '</span>';
    }

    // Get ExDivident Date for portfolio (Legacy, ported from AF)
    public function af_get_ExDividendDate($obj) {
        $val = '';
        $arSubtrades = is_array($obj->Subtrades) ? $obj->Subtrades : array();
        foreach ($arSubtrades as $arSubtrade) {
            if (isset($arSubtrade->ExDividendDate) && $arSubtrade->ExDividendDate) {
                $val = strftime("%m/%d/%Y",strtotime($arSubtrade->ExDividendDate));
            }
        }
        return $val;
    }

    // Get Options for portfolio (Legacy, ported from AF)
    public function af_get_isOption($obj) {
        $symbol = $obj->Symbol;
        if (strlen($symbol) > 10 && preg_match('/[0-9]+[cCpP][0-9]+/', $symbol)) {
            return true;
        }
        return false;
    }

    // Get stopLoss for portfolio (Legacy, ported from AF)
    public function af_get_stopLoss($obj) {
        $possible_stop_loss_columns = array(
            'Text1',
            'Text2',
            'Text3',
            'Text4',
            'Text5',
        );
        foreach ($possible_stop_loss_columns as $possible_stop_loss_column) {
            if (isset($obj->PositionSetting->$possible_stop_loss_column)) {
                $text = trim($obj->PositionSetting->$possible_stop_loss_column);
                $look_for_stop_loss_pattern = "/stop loss: .*/i";
                preg_match($look_for_stop_loss_pattern, $text, $matches);

                if (empty($matches)) {
                    continue;
                }
                $look_for_money_pattern = "/(\d+(\.\d+)?)/i";
                preg_match($look_for_money_pattern, $matches[0], $money_matches);

                if (empty($money_matches)) {
                    continue;
                }
                return '$' . $money_matches[0];
            }
        }
    }

    // Get stopLoss for portfolio (Legacy, ported from AF)
    public function af_get_stopPrice($obj) {
        $possible_matches = array('TrailingStopPrice', 'CloseBelowStop', 'HardStopPrice');
        $match = 'N/A';
        foreach ($possible_matches as $possible_match) {
            if (isset($obj->Subtrades[0]->$possible_match)) {
                setlocale(LC_MONETARY, 'en_US');
                $match = money_format('%.2n', $obj->Subtrades[0]->$possible_match) . '';
            } elseif (isset($obj->Subtrades[0]->SubtradeSetting->$possible_match)) {
                setlocale(LC_MONETARY, 'en_US');
                $match = money_format('%.2n', $obj->Subtrades[0]->SubtradeSetting->$possible_match) . '';
            }
        }
        $match = $this->af_set_dollar($match);
        return $match;
    }
}
