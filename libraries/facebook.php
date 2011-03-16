<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter Facebook Library
 * 
 * Author: Chris Harvey (Back2theMovies)
 * Website: http://www.b2tm.com
 * Email: chrish@b2tm.com
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
	
	public function logout()
	{
		$this->_unset("facebook_access_token");
	}
	
	private function _unset($key)
	{
		$this->_CI->session->unset_userdata($key);
	}
	
	public function is_logged_in()
	{
		$check = $this->call("get", "me");
		
		if($this->is_offline())
		{
			if($check)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		elseif($this->is_cookie())
		{
			if($check)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		elseif(isset($_GET['code']))
		{
			$call_url = $this->_graph_url."oauth/access_token?client_id=".$this->_app_id."&redirect_uri=".$this->_CI->session->userdata("facebook_redirect_uri")."&client_secret=".$this->_secret."&code=".$this->_CI->input->get("code");
			$curl = $this->curl_call('get', $call_url);
			$token = parse_str($curl);
			
			if(isset($access_token) || isset($expires)){
				$this->set_access_token($access_token, $expires);
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}
	
	private function is_cookie()
	{
		if(isset($_COOKIE["fbs_".$this->_app_id]))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	private function is_offline()
	{
		$sess_access_token = $this->_CI->session->userdata("facebook_access_token");
		if(is_array($sess_access_token))
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
				$this->logout();
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
	
	private function get_session()
	{
		if($this->is_cookie())
		{
			$session = array();

			parse_str(trim(
    	        get_magic_quotes_gpc()
    	          ? stripslashes($_COOKIE["fbs_".$this->_app_id])
    	          : $_COOKIE["fbs_".$this->_app_id],
    	        '"'
    	      ), $session);
          
      	  return $session;
      	  
        }
        else
        {
        	return FALSE;
        }
	}
	
	public function get_access_token()
	{
		if($this->is_cookie())
		{
			$session = $this->get_session();
			return array("token" => $session['access_token'], "expires" => $session['expires']);
		}
		elseif($this->is_offline())
		{
			return $this->_CI->session->userdata("facebook_access_token");
		}
		else
		{
			return FALSE;
		}
	}
	
	public function set_access_token($access_token, $expires)
	{
		$this->_CI->session->set_userdata('facebook_access_token', array("token" => $access_token, "expires" => $expires));
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