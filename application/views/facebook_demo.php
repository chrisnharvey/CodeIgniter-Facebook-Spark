<!DOCTYPE html>
<html lang="en" <?=facebook_html()?>>
<head>
	<meta charset="utf-8">
	<title>CodeIgniter Facebook Package Demo Page</title>

<style type="text/css">

body {
 background-color: #fff;
 margin: 40px;
 font-family: Lucida Grande, Verdana, Sans-serif;
 font-size: 14px;
 color: #4F5155;
}

a {
 color: #003399;
 background-color: transparent;
 font-weight: normal;
}

h1 {
 color: #444;
 background-color: transparent;
 border-bottom: 1px solid #D0D0D0;
 font-size: 16px;
 font-weight: bold;
 margin: 24px 0 2px 0;
 padding: 5px 0 6px 0;
}

code {
 font-family: Monaco, Verdana, Sans-serif;
 font-size: 12px;
 background-color: #f9f9f9;
 border: 1px solid #D0D0D0;
 color: #002166;
 display: block;
 margin: 14px 0 14px 0;
 padding: 12px 10px 12px 10px;
}

</style>
<?=facebook_meta($meta)?>
</head>
<body>

<h1>Welcome to the CodeIgniter Facebook Package Demo Page</h1>

<div id="fb-root"></div>
<script src="http://connect.facebook.net/en_US/all.js"></script>
<script>
   FB.init({ 
      appId:'<?=$this->facebook->_app_id?>', cookie:true, 
      status:true, xfbml:true 
   });
   FB.Event.subscribe('auth.login', function() {
     window.location.reload();
   });
</script>
<?php if($this->facebook->is_logged_in()): ?>
<p><img src="<?=facebook_picture($user->id)?>"><br><b>Hello <?=$user->name?>, welcome to the CodeIgniter Facebook Package demo page.</b></p>
<?php else: ?>
<p>Login using JavaScript & XFBML: <fb:login-button perms="<?=facebook_scope()?>">Connect with Facebook</fb:login-button></p>
<p>Login without using JavaScript & XFBML: <a href="<?=$this->facebook->login_url()?>"><img src="http://static.ak.fbcdn.net/rsrc.php/zB6N8/hash/4li2k73z.gif"></a></p>
<?php endif; ?>

<p>This page is completely Facebook enabled. We have Open Graph meta tags embedded in this page, and we can call the Facebook Graph API like this:</p>
<code>$this->facebook->call("get", "me");</code>

<p>You can also POST or GET parameters in an array like this:</p>
<code>$this->facebook->call("post", "me/feed", array("message" => "This is a message from the CodeIgniter Facebook Package"));</code>

<p>You can also upload images and other media to Facebook like this:</p>
<code>
$image = "@".realpath(BASEPATH."../image.jpg"); // This locates a JPEG located in the root directory of your CodeIgniter setup (where your index.php file is located)
<br>$this->facebook->call("post", "me/photos", array("source" => $image, "message" => "This is an image uploaded from the CodeIgniter Facebook Package"));
</code>
<b>Notice the "@" symbol before the URL, remember to include this whenever uploading media. For example "@/htdocs/www/images/image.jpeg"</b>

<p>You can add Open Graph data to your pages, create an array that will be passed to your view like this:</p>
<code>$data['meta'] = array("og:title" => "CodeIgniter Facebook Library", "og:type" => "website", "og:description" => "A Facebook library for CodeIgniter that allows you to make calls to the Facebook Graph API and easily integrate the meta tags for the Open Graph protocol.", "fb:app_id" => $this->config->item("facebook_app_id"));
<br>$this->load->view('facebook_demo', $data);</code>

<p>You can then parse the array in your view file with the help of the helper included in this package</p>
<code>&lt;?=facebook_meta($meta)?&gt;</code>

<p><b>All configurable options for this package are located in the config file</b></p>

<p>The best way to keep this package up-to-date is to create a "b2tm" folder inside your "third_party" folder, with a "facebook" folder in there with the contents of the entire package, you can then replace the code when a new release is available, either through "git pull" or simply copy the updated files into there. You can then add the package path using:</p>
<code>$this->load->add_package_path(APPPATH.'third_party/b2tm/facebook/application');</code>

<p>The corresponding controller for this page is found at: <b>application/controllers/facebook_demo.php</b></p>

<p>The view file for this page can be found at: <b>application/views/facebook_demo.php</b></p>

<p><br>You can get more information about the Facebook Graph API and the Open Graph protocol at: <a href="http://developers.facebook.com/docs/coreconcepts/">http://developers.facebook.com/docs/coreconcepts/</a></p>

<p><br>This package is provided for free by <a href="http://www.b2tm.com/">Back2theMovies</a>, please report any bugs or feature requests on the Back2theMovies Open Source page at <a href="http://www.b2tm.com/">http://www.b2tm.com/</a></p>

</body>
</html>