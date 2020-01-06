<?php
$html = $avatar = $byline = $postdate = $new = $plus = $lifetime = $pubs = $labels = $spacer = $coauthors = '';

if ($post_data) {
    // Look for co-authors
    $article_coauthors = $post_data['article_coauthors'];

    if ($article_coauthors) {
        $count = count($article_coauthors);
        $i = 1;
        foreach ($article_coauthors as $author) {
            $conjunction = $i == $count ? ' and ' : ', ' ;
            $name = $author['user_firstname'] . ' ' . $author['user_lastname'];
            $url = get_author_posts_url($author['ID']);
            $coauthors .= $conjunction . '<a href="' . $url . '">' . $name . '</a>';
            $i++;
        }
    }

    $avatar = !empty($post_data['article_author_image']) ? '<div class="article-meta-image">' . $post_data['article_author_image'] . '</div>' : '';
    $byline = !empty(trim($post_data['article_author'])) && !empty($post_data['article_author_url']) ? '<span class="meta-post-author">By <a href="' . $post_data['article_author_url'].'">' . $post_data['article_author'] . '</a>' . $coauthors . '</span>' : '';
    $postdate = '<span class="meta-post-date">Posted <span class="meta-date">' . $post_data['article_date'] . '</span></span>';
    $new = !empty($post_data['article_new']) && $post_data['article_new'] != '' ? '<span class="alert label label-new">New ' . $post_data['article_category_name'] . '</span>' : '';
    $plus = !empty($post_data['article_plus']) && $post_data['article_plus'] != '' ? '<span class="alert label label-' . sanitize_title($post_data['article_plus_name']) . '">' . $post_data['article_plus_name'] . '</span>' : '';
    $lifetime = !empty($post_data['article_lifetime']) && $post_data['article_lifetime'] != '' ? '<span class="alert label">Lifetime Members Only</span>' : '';
    $publications = !empty($post_data['article_publications']) ? $post_data['article_publications'] : '';

    if ($new || $plus || $lifetime) {
        $labels = '<div class="article-meta-labels">' . $new . $plus . $lifetime . '</div>';
    }

    echo '
    <div class="article-meta">
        ' . $avatar . '
        <div class="article-meta-text">
        ' . $byline . $postdate . $labels . '
        </div>
    </div>
    ';
}