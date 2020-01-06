<?php
// Get social media data from Yoast plugin
$social = get_option('wpseo_social');

$social_links = array();

// Assign variables to data to match name of svg icon
// NOTE: naming convention varies for Yoast
!empty($social['facebook_site']) ? $social_links['facebook'] = $social['facebook_site'] : '';
!empty($social['twitter_site']) ? $social_links['twitter'] = 'https://www.twitter.com/' . $social['twitter_site'] : '';
!empty($social['linkedin_url']) ? $social_links['linkedin'] = $social['linkedin_url'] : '';
!empty($social['youtube_url']) ? $social_links['youtube'] = $social['youtube_url'] : '';
!empty($social['google_plus_url']) ? $social_links['google'] = $social['google_plus_url'] : '';
!empty($social['pinterest_url']) ? $social_links['pinterest'] = $social['pinterest_url'] : '';

// Remove missing
foreach ($social_links as $k => $v) {
    if (empty($v)) {
       unset($social_links[$k]);
    }
}

// Create list
if (!empty($social_links)) {
    $template_directory_uri = PARENT_PATH_URI;
    $social_html = '<ul class="social-links">';
    foreach ($social_links as $k => $v) {
        $social_html .= '
            <li>
                <a href="' . $v . '" target="_blank">
                    <svg class="icon icon-' . $k . '">
                        <use xlink:href="' . $template_directory_uri . '/img/symbol-defs.svg#icon-' . $k . '"></use>
                    </svg>
                </a>
            </li>
        ';
    }
    $social_html .= '</ul>';
    echo $social_html;
}