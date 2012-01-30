<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter Facebook Spark
 * 
 * Author: Chris Harvey (Back2theMovies)
 * Website: http://www.chrisnharvey.com/code
 * Email: chris@chrisnharvey.com
 *
 * Originally developed for Back2theMovies (http://www.b2tm.com)
 * 
 **/
 
class Facebook {

	public $_graph_url = "https://graph.facebook.com/";
	public $_app_id;
	private $_secret;
	
	function __construct()
	{
		$this->_CI =& get_instance();
		
		$this->_CI->load->config('facebook');
		$this->_CI->load->library("session");
		$this->_CI->load->helper("url");
		
		$this->_app_id = $this->_CI->config->item('facebook_app_id');
		$this->_secret = $this->_CI->config->item('facebook_secret');
		$this->_default_scope = $this->_CI->config->item('facebook_default_scope');
		$this->_cookie_name = "fbsr_".$this->_app_id;
		
		if(!$this->_CI->session->userdata("facebook_scope"))
		{
			$this->set_scope($this->_default_scope);
		}
		
		if(!$this->_CI->session->userdata("facebook_redirect_uri"))
		{
			$this->set_redirect_uri(current_url());
		}
	}
	
	public function login_url()
	{
		$scope = $this->_CI->session->userdata("facebook_scope");
		$redirect_uri = $this->_CI->session->userdata("facebook_redirect_uri");
		
		if(!isset($scope))
		{
			$scope = $this->_default_scope;
		}
		
		if(empty($scope))
		{
			$scope_string = "";
		}
		else
		{
			$scope_string = "&scope=".$scope;
		}
		
		if(empty($redirect_uri))
		{
			$callback_string = "&redirect_uri=".site_url();
		}
		else
		{
			$callback_string = "&redirect_uri=".$redirect_uri;
		}
		
		if(!isset($redirect_uri))
		{
			$redirect_uri = "";
		}
		
		return "https://www.facebook.com/dialog/oauth?client_id=".$this->_app_id.$callback_string.$scope_string;
	}
	
	private function _unset($key)
	{
		$this->_CI->session->unset_userdata($key);
	}
	
	public function is_logged_in()
	{
		$check = $this->call("get", "me");
		
		if($this->get_access_token() && $check)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	public function call($method, $uri, $params = array()){
	
		$token = $this->get_access_token();
		$token = $token['token'];
		
		$url_string = $this->_graph_url.$uri."?access_token=".$token;
		
		if($method == "get")
		{
			foreach($params as $param => $value)
			{
				$url_string .= "$".$param."=".$value;
			}
		}
		
		if($uri == "me")
		{
			try
			{
				$response = $this->curl_call($method, $url_string, $params);
			}
			catch(facebookException $e)
			{
				$this->_unset("facebook_access_token");
				return FALSE;
			}
		}
		else
		{
			try
			{
				$response = $this->curl_call($method, $url_string, $params);
			}
			catch(facebookException $e)
			{
				$this->call("get", "me");
				return FALSE;
			}
		}
		
		$response = json_decode($response); // Decode the JSON response into an array
		
		return $response;
	}
	
	private function curl_call($method = 'get', $url, $params = array())
	{
		$ch = curl_init();
		
		if($method == "post")
		{
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}

		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		$response = curl_exec ($ch);

		curl_close ($ch);
		
		$response_a = json_decode($response);
		
		if(isset($response_a->error))
		{
			throw new facebookException($response_a->error->type." - ".$response_a->error->message);
		}
		else
		{
			return $response; // Return the response
		}
	}
	
	protected function parse_signed_request($signed_request) {
		list($encoded_sig, $payload) = explode('.', $signed_request, 2);

		// decode the data
		$sig = $this->base64_url_decode($encoded_sig);
		$data = json_decode($this->base64_url_decode($payload), true);

		if (strtoupper($data['algorithm']) !== 'HMAC-SHA256')
		{
			return NULL;
		}
		
		// check sig
		$expected_sig = hash_hmac('sha256', $payload, $this->_secret, $raw = true);
		if ($sig !== $expected_sig)
		{
			return NULL;
		}

		return $data;
	}
	
	protected static function base64_url_decode($input) {
	    return base64_decode(strtr($input, '-_', '+/'));
	  }
	
	public function get_access_token()
	{
		$sess_access_token = $this->_CI->session->userdata("facebook_access_token");
		if(!empty($sess_access_token))
		{
			return $this->_CI->session->userdata("facebook_access_token");
		}
		elseif(isset($_REQUEST['signed_request']))
		{
			$signed_request = $this->parse_signed_request($_REQUEST['signed_request']);
	    }
		elseif(isset($_COOKIE[$this->_cookie_name]))
		{
	        $signed_request = $this->parse_signed_request($_COOKIE[$this->_cookie_name]);
	    }
	
		if(isset($signed_request))
		{
			try
			{
				$call_url = $this->_graph_url."oauth/access_token?client_id=".$this->_app_id."&redirect_uri=&client_secret=".$this->_secret."&code=".$signed_request['code'];
				$curl = $this->curl_call('get', $call_url);
				$token = parse_str($curl);
				
				if(isset($access_token) && isset($expires))
				{
					$this->set_access_token($access_token, $expires);
					return $this->_CI->session->userdata("facebook_access_token");
				}
				elseif(isset($access_token) && !isset($expires))
				{
					$this->set_access_token($access_token);
					return $this->_CI->session->userdata("facebook_access_token");
				}
				else
				{
					return FALSE;
				}
			}
			catch(facebookException $e)
			{
				$this->_unset("facebook_access_token");
				return FALSE;
			}
		}
	}
	
	public function set_access_token($access_token, $expires = FALSE)
	{
		if($expires != FALSE)
		{
			$this->_CI->session->set_userdata('facebook_access_token', array("token" => $access_token, "expires" => $expires));
		}
		else
		{
			$this->_CI->session->set_userdata('facebook_access_token', array("token" => $access_token));
		}
	}
	
	public function set_redirect_uri($redirect_uri)
	{
		$this->_CI->session->set_userdata('facebook_redirect_uri', $redirect_uri);
	}
	
	public function set_scope($scope)
	{
		$this->_CI->session->set_userdata('facebook_scope', $scope);
	}

}

class facebookException extends Exception {

	function __construct($string)
	{
	    parent::__construct($string);
	}
	
	public function __toString() {
	    return "exception '".__CLASS__ ."' with message '".$this->getMessage()."' in ".$this->getFile().":".$this->getLine()."\nStack trace:\n".$this->getTraceAsString();
	}
}

/* End of file */
