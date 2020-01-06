<?php
/**
 * 
 *	Database Wrapper Class
 *
 *	Mostly wrappers for wordpress functions. Used by the base plugin to interact with the wordpress database
 *
 */
class agora_database_wrapper{

	/**
	 * @var string prefix for the wp_options keys to store term meta
	 */
	private $meta_prefix = 'agora_term_meta_';

	/**
	 * Function to store 'term' meta. At this time, wordpress does not have a
	 * term meta system of it's own so we're using the wp_options table
	 *
	 * @param string $term_slug
	 * @param string $value
	 *
	 * @return mixed
	 */
	public function set_term_meta($term_slug, $value = ''){
		if($value == ''){
			return $this->delete_option($this->meta_prefix . $term_slug);
		}else{
			return $this->update_option($this->meta_prefix . $term_slug, $value);
		}
	}

	/**
	 * Function to retrieve 'term' meta from the wp_options table
	 * @param string $term_slug
	 * @param null $default
	 *
	 * @return mixed
	 */
	public function get_term_meta($term_slug, $default = null){
		return $this->get_option($this->meta_prefix . $term_slug, $default);
	}

	/**
	 * Function to delete 'term' meta.
	 * @param string $term_slug
	 *
	 * @return mixed
	 */
	public function delete_term_meta($term_slug){
		return $this->delete_option($this->meta_prefix . $term_slug);
	}

	/**
	 * Method to wrap the Wordpress add_option function
	 *
	 * @param string $option A string value for the option to write
	 * @param string $value A Value for the option to save
	 * @param boolean $deprecated It's deprecated, check the wordpress docs
	 * @param boolean $autoload Check the wordpress docs 
	 * @return mixed
	 */
	function add_option($option, $value ='', $deprecated = null, $autoload = null){
		return add_option($option, $value, $deprecated, $autoload);
	}

	/**
	 * Method to wrap the Wordpress get_option function
	 *
	 * @param string $option A string value for the option to write
	 * @param string/obj/array $default a default value to return if none is found	 
	 * @return mixed
	 */
	function get_option($option, $default = null){
		return get_option($option, $default);
	}

	/**
	 * Method to wrap the Wordpress update_option function
	 *
	 * @param string $option A string value for the option to write
	 * @param string/obj/array $newvalue A Value for the option to save
	 * @return mixed
	 */
	function update_option($option, $new_value){
		return update_option($option, $new_value);
	}

	/**
	 * Method to wrap the Wordpress delete_option function
	 *
	 * @param string $option A string value for wordpress to delete
	 * @return mixed
	 */
	function delete_option($option){
		return delete_option($option);
	}
}