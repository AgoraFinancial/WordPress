<?php global $af_templates; ?>
<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://s3.amazonaws.com">
    <title><?php wp_title(); ?></title>
    <?php
        if($noindex) echo '<meta name="robots" content="noindex, nofollow">';
        $af_templates->af_favicons();
        $af_templates->af_scripts_head();
    ?>
</head>