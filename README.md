#CodeIgniter Facebook Spark

_Deprecated: Looking for a new maintainer for this package. Open an issue if interested._

We can call the Facebook Graph API like this:

	$this->facebook->call("get", "me");
You can also POST or GET parameters in an array like this:

	$this->facebook->call("post", "me/feed", array("message" => "This is a message from the CodeIgniter Facebook Package"));
You can also upload images and other media to Facebook like this:

	$image = "@".realpath(BASEPATH."../image.jpg"); // This locates a JPEG located in the root directory of your CodeIgniter setup (where your index.php file is located) 
	$this->facebook->call("post", "me/photos", array("source" => $image, "message" => "This is an image uploaded from the CodeIgniter Facebook Package"));
Notice the "@" symbol before the URL, remember to include this whenever uploading media. For example "@/htdocs/www/images/image.jpeg"
You can add Open Graph data to your pages, create an array that will be passed to your view like this:

	$data['meta'] = array("og:title" => "CodeIgniter Facebook Spark", "og:type" => "website", "og:description" => "A Facebook spark for CodeIgniter that allows you to make calls to the Facebook Graph API and easily integrate the meta tags for the Open Graph protocol.", "fb:app_id" => $this->config->item("facebook_app_id")); 
	$this->load->view('facebook_demo', $data);
You can then parse the array in your view file with the help of the helper included in this package

	<?=facebook_meta($meta)?>
All configurable options for this package are located in the config file which can be found in the sparks folder.
