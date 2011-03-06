<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Facebook_demo extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->library("facebook");
		$this->load->helper("facebook");
	}

	function index()
	{
		if($this->facebook->is_logged_in()){
			$data['user'] = $this->facebook->call("get", "me");
		}
		
		$data['meta'] = array(
						"og:title" => "CodeIgniter Facebook Library",
						"og:type" => "website",
						"og:description" => "A Facebook library for CodeIgniter that allows you to make calls to the Facebook Graph API and easily integrate the meta tags for the Open Graph protocol.",
						"fb:app_id" => $this->config->item("facebook_app_id")
						);
		
		$this->load->view('facebook_demo', $data);
	}
}

/* End of file */