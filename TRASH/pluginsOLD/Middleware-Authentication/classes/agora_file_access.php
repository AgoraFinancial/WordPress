<?php

/**
 * Class agora_file_access
 */

/**
 * Class: agora_file_access constructor.
 * @author: Threefold Systems
 */
class agora_file_access{
	protected $core;

	/**
	 * Description: Constructor
	 */
	function __construct( agora_core_framework $core ) {
		$this->core = $core;

		add_action('template_redirect', array($this, 'handle_file_requests'));
	}

    /**
     * Description: get the path of the attachemnt file
     * @method find_wp_config_path
     * @return null|string
     */
    function find_wp_config_path() {
        $dir = dirname(__FILE__);
        do {
            if( file_exists($dir."/wp-config.php") ) {
                return $dir;
            }
        } while( $dir = realpath("$dir/..") );
        return null;
    } 

	/**
	 * Description: Hooks into template_redirect action, figures out if there's a file download request based on the file_path variable The file_path variable is added by the .htaccess rule(s)
	 * @method handle_file_requests
	 * @return bool|void
	 */
	function handle_file_requests() {
		$get_file_path = ( isset( $_GET[ 'file_path' ] ) ? sanitize_text_field( $_GET[ 'file_path' ] ) : '' );

		if ( $get_file_path ) {
		    //assign to var as we do not want to mess with the GET vars.
            $file_location = $get_file_path;

		    //pop the tailing slash off the url
            if(substr($file_location, -1) == '/') {
                $file_location = substr($file_location, 0, -1);
            }

			$filename = basename($file_location);
			$post_id = $this->get_post_id($filename);
                if ($post_id) {
                    $auth_container = new agora_auth_container($post_id);
                    $auth_container = apply_filters('agora_middleware_check_permission', $auth_container);

					if ( $auth_container->is_allowed() || in_array( 'administrator' , ( array ) wp_get_current_user()->roles ) ) {
                        // Deliver the file
                        return $this->deliver_file($file_location);
                    } else {
                        // Bounce the user to the parent post
                        $url = get_permalink($post_id);
                        if ($url) {
                            return wp_redirect(apply_filters('agora_file_download_access_denied', $url));
                        } else {
                            // If we can't find a URL for the post ID, bounce them to the homepage
                            return wp_redirect(apply_filters('agora_file_download_home_redirect', get_bloginfo('url')));
                        }
                    }
                }

			// There's no parent post ID, so no way to tell if it's password protected. Just return the file.
			return $this->deliver_file($file_location);
		}
	}

	/**
	 * Description: Deliver the requested file.
	 * @method deliver_file
	 * @param $filepath
	 */
	function deliver_file($filepath){
		$abs_path = $this->find_wp_config_path() . $filepath;
		if(!is_file($abs_path)){
			global $wp_query;
			$wp_query->is_404 = true;
			status_header(404);
			return;
		}else{
			status_header( 200 );
			// a lot of the rest of this is from blogs.php, but slightly modded where deemed necessary
			$mime = wp_check_filetype($abs_path);

			if ($mime['type'] === false && function_exists('mime_content_type')) {
				$mime['type'] = mime_content_type($abs_path);
			}

			if ($mime['type'] != false) {
				$mimetype = $mime[ 'type' ];
			} else {
				$ext = pathinfo($abs_path,PATHINFO_EXTENSION);
				$mimetype = "image/$ext";
			}


			// the rest is unmodified from blogs.php
			@header( 'Content-type: ' . $mimetype ); // always send this
			@header( 'Content-Length: ' . filesize( $abs_path ) );

			@header('Content-disposition: inline; filename='.basename($abs_path));

			$last_modified = gmdate('D, d M Y H:i:s', filemtime( $abs_path ));
			$etag = '"' . $this->core->tfs_hash($last_modified) . '"';
			@header( "Last-Modified: $last_modified GMT" );
			@header( 'ETag: ' . $etag );
			@header( 'Expires: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT' );

			readfile($abs_path);
			// forcefully exit, needed to stop the rest of WordPress from processing
			exit;
		}

	}

	/**
	 * Description: Take the file name, and find the ID of the parent post.
	 * @method get_post_id
	 * @param $filename
	 * @return bool
	 */
	function get_post_id($filename){
		global $wpdb;
		$file_basename = substr($filename,0,strpos($filename,'.'));
		$query = $wpdb->prepare("SELECT ID from {$wpdb->posts}
			  WHERE post_status in ('publish', 'private')
			  AND (post_type != 'revision' AND post_type != 'attachment')
			  AND ID = (SELECT post_parent FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_name = %s LIMIT 1)", $file_basename);

		$parent_post_id = $wpdb->get_var($query);

		if(empty($parent_post_id)) {
			return false;
		}else{
			return $parent_post_id;
		}
	}
}
