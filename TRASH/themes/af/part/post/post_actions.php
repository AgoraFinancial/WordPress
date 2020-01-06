<?php
global $af_pages;

$template_directory_uri = PARENT_PATH_URI;

$saved_flag = false;
$saved_posts = $af_pages->af_get_user_articles();

if (in_array($post->ID, $saved_posts)) {
    $saved_flag = true;
}

$has_pdf = isset($post_data['article_download_url']) && $post_data['article_download_url'] ? true : false ;
$pdf_url = add_query_arg(array('pdf' => 1), get_permalink());   

$dataAttributes = '';

if (is_user_logged_in()) {
    global $af_users;
    $aid = get_the_ID();
    $uid = $af_users->af_get_user_data('id');
    $token = $af_users->af_get_user_data('af_user_token');
    if ($aid && $uid && $token) {
        $dataAttributes = ' data-uid="'.$uid.'" data-aid="'.$aid.'" data-token="'.$token.'" ';
    }
}
?>
<div class="button-group post-actions">
    <?php if ($saved_flag) { ?>
    <span class="button disabled">
        <svg class="icon icon-bookmark">
            <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-bookmark"></use>
        </svg>
        <span>Saved</span>
    </span>
    <?php } else { ?>
    <span class="button button--save"<?php echo $dataAttributes; ?>>
        <svg class="icon icon-bookmark">
            <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-bookmark"></use>
        </svg>
        <span>Save For Later</span>
    </span>
    <?php } ?>
    <span class="button button--print">
        <svg class="icon icon-print">
            <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-print"></use>
        </svg>
        <span>Print</span>
    </span>
    <span class="button button--resize">
        <svg class="icon icon-resize">
            <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-resize"></use>
        </svg>
        <span>Resize Text</span>
    </span>   
    <?php if ($has_pdf) { ?>
        <a class="button alert button--download" target="_blank" href="<?php echo $pdf_url; ?>">
            <svg class="icon icon-file-pdf">
                <use xlink:href="<?php echo $template_directory_uri; ?>/img/symbol-defs.svg#icon-file-pdf"></use>
            </svg>
            <span>Download PDF</span>
        </a>
    <?php } ?>
</div>

<?php if ($has_pdf && has_post_thumbnail()) { ?>
    <a href="<?php echo $pdf_url; ?>" target="_blank" class="pdf-featured-image alignright">
        <?php the_post_thumbnail('medium'); ?>
    </a>
<?php } ?>