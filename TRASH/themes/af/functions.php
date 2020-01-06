<?php

// Assign constant paths
if ( !defined( 'PARENT_PATH' ) ) define( 'PARENT_PATH', get_template_directory() );
if ( !defined( 'PARENT_PATH_URI' ) ) define( 'PARENT_PATH_URI', get_template_directory_uri() );
if ( !defined( 'CHILD_PATH' ) ) define( 'CHILD_PATH', get_stylesheet_directory() );
if ( !defined( 'CHILD_PATH_URI' ) ) define( 'CHILD_PATH_URI', get_stylesheet_directory_uri() );

// Load all core files, parent files & instantiate
require_once PARENT_PATH . '/inc/core/AF_Base.php';
require_once PARENT_PATH . '/inc/core/AF_Theme.php';

// Load remaining files
foreach(glob(dirname(__FILE__) . '/inc/theme/*.php') as $file) {
    require_once $file;
}

// Instantiate classes as separate file to make extensible in child
require_once PARENT_PATH . '/inc/core/AF_Instantiate.php';