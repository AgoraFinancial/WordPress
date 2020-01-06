<?php
/**
 *	A class to wrap MC calls into nice php methods and return shiny objects
 *
 *	@package agora_core_framework
 *	@subpackage agora_mc_wrapper
 *	@author Adam Wilson
 *
 **/

class agora_mc_wrapper extends agora_api_wrapper{
    /**
     * Object container for the logging system.
     * @var object
     */
    private $log;

    /**
     * @var
     */
    private $core;

    /**
     *  PHP Constructor
     *
     * @param array $config The configuration, loaded by the base class and passed to the MW wrapper on instantiation
     *
     */
    public function __construct($config, agora_core_framework $core){

        $this->config = $config;

        $this->url = trim($config['mc_url']);
        $this->token = trim($config['mc_token']);
        $this->orgid = trim($config['mc_orgid']);

        if ( ! empty( $config['mc_list'] ) ) {
            $this->list = trim($config['mc_list']);
        } else {
            $this->list = "";
        }

        $this->mc_is_paid = ( isset( $config['mc_is_paid'] ) ? trim($config['mc_is_paid']) : '' );

        $this->log = new agora_log_framework();

        $this->core = $core;

        $this->http_args = array('headers' => array('token' => $this->token), 'sslverify' => false);
        if($this->mc_is_paid == 1){
            $this->url = 'https://' . $this->url . '/mcs-paid/';
        }else{
            $this->url = 'https://' . $this->url . '/mcs/';
        }

        add_filter( 'http_request_timeout', array($this,'wp_timeout_extend' ));
    }

    /**
     * extended post time because updating can take more then 5 seconds
     *
     * @param $time
     * @return int
     */
    function wp_timeout_extend( $time )
    {
        // Default timeout is 5
        return 15;
    }

    /**
     * get all of the lists for a specific org
     *
     * @return array
     */
    function get_all_lists_by_orgid(){
        $url = $this->url . 'agora/list/orgid/' . $this->orgid;
        return $this->_get($url);
    }

    /**
     * get a list of all the mailings for a specific org
     *
     * @param $org_id
     * @return array
     */
    function get_all_mailings_by_orgid(){
        $url = $this->url . 'mailing/orgid/' . $this->orgid;
        add_filter( 'http_request_timeout', array($this,'message_central_timeout' ));
        $result = $this->_get($url);
        remove_filter( 'http_request_timeout', array($this,'message_central_timeout' ));
        return $result;
    }

    /**
     * extended mailing timeout to account for large volumes of mailings
     *
     * @param $time
     * @return int
     */
    public function message_central_timeout( $time )
    {
        // Default timeout is 5
        return 60;
    }

    /**
     * get a list of content for an org
     *
     * @return array
     */
    public function get_content_by_orgid(){
        $url = $this->url . 'content/orgid/' . $this->orgid;
        return $this->_get($url);
    }

    /**
     * get mailing by id
     *
     * @param $mailing_id
     *
     * @return array
     */
    public function get_mailing_by_id($mailing_id){
        $url = $this->url . 'mailing/orgid/' . $this->orgid . '/mid/' . $mailing_id;
        return $this->_get($url);
    }

    /**
     * get mailing by id
     *
     * @param $mailing_id
     *
     * @return array
     */
    public function get_mailings_by_list_id_org_id($list_id){
        $url = $this->url . 'agora/mailing/orgid/' . $this->orgid . '/lid/' . $list_id;
        return $this->_get($url);
    }

    /**
     * associate a specific mailing with a list
     *
     * @param $org_id
     * @param $mailing_id
     * @param $list_id
     * @return array|bool|mixed|WP_Error
     */
    public function put_associate_mailing_with_agora_list($org_id, $mailing_id, $list_id){
        $url = $this->url . 'agora/associatemailing/orgid/' . $org_id . '/mid/' . $mailing_id . '/lid/' . $list_id;
        $payload = array();
        return $this->_put($url, $payload);
    }

    /**
     * Create a mailing in MC
     *
     * @param $content_id
     * @param $campaign
     * @param $type
     *
     * @return string
     */
    public function put_create_mailing($name, $content_id, $campaign, $type){
        $url = $this->url . 'mailing/orgid/' . $this->orgid;
        $payload = array('name' => $name, 'campaign' => $campaign, 'segments' => array(array('content' => $content_id)),
            'type' => $type, 'startDate' => date('m/d/Y h:i:s a', time()), 'state' => 'created');

        return $this->_post($url, $payload);
    }

    /**
     * Update a MC mailing
     * @param $mailing
     *
     * @return array|mixed|WP_Error
     */

    public function put_update_mailing($mailing){
        $url = $this->url . 'mailing/orgid/' . $this->orgid;
        $payload = $mailing;

        return $this->_post($url, $payload);
    }

    /**
     * Create content for a mailing in MC
     *
     * @param $content
     * @param $content_name
     *
     * @return int
     */
    public function put_create_content($content, $content_name, $headers){
        $url = $this->url . 'content/orgid/' . $this->orgid;
        $payload = array('name' => $content_name, 'html' => $content, 'headers' =>  $headers);

        return $this->_post($url, $payload);
    }

    /**
     * update content in mc
     * 
     * @param $content_id
     * @param $content
     * @param $content_name
     * @param $headers
     * @return array|mixed|WP_Error
     */
    function put_update_content($content_id, $content, $content_name, $headers){
        $url = $this->url . 'content/orgid/' . $this->orgid . '/cid/' . $content_id;
        $payload = array( 'name' => $content_name, 'html' => $content, 'headers' =>  $headers);

        return $this->_post($url, $payload);
    }

    /**
     * Send an email using the customers email address
     *
     * @param $email
     * @param $mailing_id
     * @param $context array of variables
     *
     * @return int
     */
    public function put_trigger_mailing($mailing_id, $email, $context){
        $url = $this->url . 'mailing/trigger/orgid/' . $this->orgid . '/mid/' . $mailing_id;
        $payload = array('email' => $email, 'context' => $context);

        return $this->_post($url, $payload);
    }

    /**
     *	Ajax method to test middlware connectivity
     *
     *	@param void
     *	@return void
     **/
    function ajax_mc_ping_test(){
        $post_call = ( isset( $_POST[ 'call' ] ) ? sanitize_text_field( $_POST[ 'call' ] ) : '' );

        if($post_call == 'mc_ping_test'){
            $result = $this->mc_ping_test();
            echo $result;
        }
        die();
    }

    /**
     *	Method to test MC connectivity.
     *
     *	@param void
     *	@return string of HTML that's returned from default mc call, else a JSON object of error information
     **/
    function mc_ping_test(){
        // Just grab the base url and return the result. Todo: set up some sort of timeout to catch
        $url = $this->url;

        $result = wp_remote_get($url);

        if($result['response']['code'] == 200){
            return wp_remote_retrieve_body($result);
        }else{

            return "Response Code: " . $result['response']['code'];

        }
    }

    /**
     * Helper Method for POST requests
     *
     * @param $url
     * @param $payload
     *
     * @return array|mixed|WP_Error
     */
    private function _post($url, $payload){
        $this->log->info('Middleware POST request to: ' . $url);

        $tfs_payload = array(
            't' => 'event', //Hit type
            'ec' => 'MC POST Call', //Event Category
            'ea' => $url, //Event Action
        );

        $url = esc_url_raw($url);

        $request_data = $this->http_args;
        $request_data['body'] = json_encode($payload);
        $request_data['headers']['Content-Type'] = 'application/json';

        $this->log->info('Request data', $request_data);

        $result = wp_remote_post($url, $request_data);

        if(is_wp_error($result)){
            $tfs_payload['el'] = $result;
            $this->core->tfs_monitor($tfs_payload);
            $this->log->error('WP Error', $result);
            return $result;

        }elseif($result['response']['code'] == 200){
            $tfs_payload['el'] = $result['response']['message'];
            $this->core->tfs_monitor($tfs_payload);
            $response_content = json_decode(wp_remote_retrieve_body($result));
            $this->log->info('Response ' . $result['response']['code'], $response_content);
            return ($response_content == null OR $response_content == '') ? true : $response_content;

        }else{
            $tfs_payload['el'] = $result['body'];
            $this->core->tfs_monitor($tfs_payload);
            $this->log->error('Result: ', $result);
            return new WP_Error('request_failed', __('Message Central POST request failed'));
        }
    }

    private function _put($url){
        $this->log->info('Middleware POST request to: ' . $url);

        $tfs_payload = array(
            't' => 'event', //Hit type
            'ec' => 'MC PUT Call', //Event Category
            'ea' => $url, //Event Action
        );

        $url = esc_url_raw($url);

        $request_data = $this->http_args;
        //$request_data['body'] = json_encode($payload);
        $request_data['headers']['Content-Type'] = 'application/json';
        $request_data['method'] = 'PUT';

        $this->log->info('Request data', $request_data);

        $result = wp_remote_request($url, $request_data);

        if(is_wp_error($result)){
            $tfs_payload['el'] = $result;
            $this->core->tfs_monitor($tfs_payload);
            $this->log->error('WP Error', $result);
            return $result;

        }elseif($result['response']['code'] == 200){
            $tfs_payload['el'] = $result['response']['message'];
            $this->core->tfs_monitor($tfs_payload);
            $response_content = json_decode(wp_remote_retrieve_body($result));
            $this->log->info('Response ' . $result['response']['code'], $response_content);
            return ($response_content == null OR $response_content == '') ? true : $response_content;

        }else{
            $tfs_payload['el'] = $result['body'];
            $this->core->tfs_monitor($tfs_payload);
            $this->log->error('Result: ', $result);
            return new WP_Error('request_failed', __('Message Central POST request failed'));
        }
    }

    /**
     *	A helper method to reduce repetition
     *
     *	@param string $url
     *	@return array Associative array of returned data. Returns WP_Error object on error
     **/
    private function _get($url){
        $this->log->info('Middleware GET Request to: ' . $url);

        $tfs_payload = array(
            't' => 'event', //Hit type
            'ec' => 'MC GET Call', //Event Category
            'ea' => $url, //Event Action
        );

        $url = esc_url_raw($url);
        $result = wp_remote_get($url, $this->http_args);

        if(is_wp_error($result)){
            $tfs_payload['el'] = $result;
            $this->core->tfs_monitor($tfs_payload);
            /**
             * WP error can happen if we can't connect or get internal server errors etc.
             */
            $this->log->error('WP Error', $result);
            return $result;

        }elseif( $result['response']['code'] == 422 OR strlen(wp_remote_retrieve_body($result)) < 10){
            $tfs_payload['el'] = $result['body'];
            $this->core->tfs_monitor($tfs_payload);
            $this->log->error('Result: ', $result);
            return new WP_Error('not_found', __('No result returned'));

        }elseif($result['response']['code'] == 200){
            $tfs_payload['el'] = $result['response']['message'];
            $this->core->tfs_monitor($tfs_payload);
            /**
             * This is a successful call and will return a php object
             */
            $response_content = json_decode(wp_remote_retrieve_body($result));
            $this->log->info('Response', $response_content);
            return $response_content;

        }else{
            $tfs_payload['el'] = $result;
            $this->core->tfs_monitor($tfs_payload);

            /**
             * Some other thing happened, log an error
             */
            $this->log->error($url, $result);

        }
    }
}