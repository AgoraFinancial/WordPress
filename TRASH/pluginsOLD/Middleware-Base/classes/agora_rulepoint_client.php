<?php

/**
 * Class agora_rulepoint_client
 * Client class to interact with rulepoint/eventing API & Queues
 */
class agora_rulepoint_client extends agora_api_wrapper{

	public $enabled = false;
	private $log;
	public $config;


	function __construct($config, $production) {

		$this->config = $config;
		$this->enabled = ($config['eventing_enabled'] == 1) ? true : false;

		$this->log = new agora_log_framework();
		if($production == 1){
			$this->url = trim($config['prod_event_endpoint']);
			$this->token = trim($config['event_prod_token']);
		}else{
			$this->url = isset( $config['uat_event_endpoint'] ) ? trim( $config['uat_event_endpoint'] ) : '';
			$this->token = isset( $config['event_uat_token'] ) ? trim( $config['event_uat_token']) : '';
		}

		if(!$this->url OR !$this->token) $this->enabled = false;
		$this->url = 'https://' . $this->url;
		$this->login_queue	= ( ! empty( $config[ 'login_event_queue' ] ) ? $config['login_event_queue'] : '' );
		$this->http_args = array('headers' => array('token' => $this->token), 'sslverify' => false, 'blocking' => false);
		add_filter('agora_middleware_login_event', array($this, 'login_event'));
	}

	/**
	 * Hooks to the agora_middleware_login_event filter to push login events to Rulepoint
	 * @param $params
	 *
	 * @return mixed
	 */
	function login_event($params){
		extract($params);
		if(isset($cvi) AND isset($authgroup) AND isset($customer_number) AND isset($url)){
			$payload = array(
				'cviNbr' => $cvi,
				'authGroup' => $authgroup,
				'customerNumber' => $customer_number,
				'ip' => $this->get_user_ip(),
				'url' => $url
			);

			$api_call = $this->url . '/' . $this->login_queue;

			$this->_post($api_call, $payload);
		}
		return $params;
	}

	private function _post($url, $payload){
		if($this->enabled){
			$this->log->info('Rulepoint POST to: ' . $url);
			$url = esc_url_raw($url);

			$request_data = $this->http_args;
			$request_data['body'] = json_encode($payload);
			$request_data['headers']['Content-Type'] = 'application/json';

			$result = wp_remote_post($url, $request_data);
			$this->log->info('Response ',  $result);
		}
	}
}
