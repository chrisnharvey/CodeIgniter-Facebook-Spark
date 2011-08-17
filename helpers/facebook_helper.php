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

function facebook_meta($meta)
{
	if(isset($meta))
	{
		$return = "";
		
		foreach($meta as $property => $content)
		{
			$return .= "<meta property='".$property."' content='".$content."' />\n";
		}
		
		return $return;
	}
	else
	{
		return FALSE;
	}
}
	
function facebook_picture($user = 'facebook')
{
	$CI = & get_instance();
	
	return $CI->facebook->_graph_url.$user."/picture";
}

function facebook_scope()
{
	$CI = & get_instance();
	
	return $CI->session->userdata("facebook_scope");
}

function facebook_html()
{
	return 'xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:fb="http://www.facebook.com/2008/fbml"';
}

/* End of file */