<?php
global $af_posts, $af_templates;

$post_data = $af_posts->af_single_post_data($post);
$pdf_url = isset($post_data['article_download_url']) && $post_data['article_download_url'] ? $post_data['article_download_url'] : '' ;

// If not PDF, exit
if(!$pdf_url) {
    header("HTTP/1.1 307 Temporary Redirect");
    header("Location: ".site_url());
    exit();
}

// Check for iOS devices
$iPod = stripos($_SERVER['HTTP_USER_AGENT'],'iPod');
$iPhone = stripos($_SERVER['HTTP_USER_AGENT'],'iPhone');
$iPad = stripos($_SERVER['HTTP_USER_AGENT'],'iPad');
$Safari = stripos($_SERVER['HTTP_USER_AGENT'],'Safari');
$Chrome = stripos($_SERVER['HTTP_USER_AGENT'],'Chrome');

// If device is iOS, use Google doc viewer
if($iPod || $iPhone || $iPad || $Safari && !$Chrome) {
    $pdf_url = 'https://docs.google.com/gview?url=' . $pdf_url . '&embedded=true';
} 

$af_templates->af_head(true);

?>

<body <?php body_class('pdf-view'); ?>>

    <iframe id="pdfIssue" src="<?php echo $pdf_url; ?>" width="100%" height="100%" frameborder="0" allowtransparency="true" scrolling="yes" allowfullscreen=""></iframe>

    <?php $af_templates->af_scripts_footer(); ?>

</body>