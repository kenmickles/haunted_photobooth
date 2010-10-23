#!/php -q
<?php  /*  >php -q server.php  */

require 'header.php';
require_once 'includes/WebSocketServer.php';

// set daemon-friendly PHP options
error_reporting(E_ALL);
set_time_limit(0);
//ob_implicit_flush();

// new WebSocketServer( socket address, socket port, callback function )
$webSocket = new WebSocketServer('127.0.0.1', 8080, 'capture');
$webSocket->run();

function capture($user, $count, $server) {
	$photo_path = PHOTO_PATH;
	$capture_cmd = CAPTURE_CMD;
	
	$base_name = time();
	$files = array();
	
	for ( $i = 1; $i <= $count; $i++ ) {
		$server->send($user->socket, 'flash');
		
		$photo_file = $base_name.'_'.$i.'.jpg';
		system($capture_cmd.' '.$photo_path.$photo_file);
		
		// TODO: photoboothify and spookify photos
		
		$server->send($user->socket, 'photos/'.$photo_file);
		
		$files[] = $photo_path.$photo_file;
	}
	
	// TODO: combine photos into one file and upload_photo
	if ( $combined_file = combine_photos($files) ) {
		print_r(upload_photo($combined_file));
	}
}

function combine_photos($files) {
	// resize each photo to 500x375
	
	$width = '516';
	$height = count($files) * 391;
	
	$image = imagecreatetruecolor($width, $height);
	$white = imagecolorallocate($image, 255, 255, 255);
	imagefilledrectangle($image, 0, 0, $width, $height, $white);
	$text_color = imagecolorallocate($image, 233, 14, 91);
	imagestring($image, 1, 5, 5,  "A Simple Text String", $text_color);
	
	//imagecopymerge ( resource $dst_im , resource $src_im , int $dst_x , int $dst_y , int $src_x , int $src_y , int $src_w , int $src_h , int $pct )
	
	foreach ( $files as $i => $file ) {
		$photo = imagecreatefromjpeg($file);
		imagecopymerge($image, $photo, 8, (($i*375) + 8), 0, 0, )
	}
	
	ob_start();
	imagejpeg($image);
	imagedestroy($image);
	$image_data = ob_get_contents();
	ob_end_clean();
	
	$tmp_file = PHOTO_PATH.'combined_'.md5(implode(',', $files)).'.jpg';
	echo $tmp_file."\n";
	
	file_put_contents($tmp_file, $image_data);
	
	return $tmp_file;
}

function upload_photo($file) {
	//$file = 'photos/manatees.jpg';
	
	// get FB access token from settings.ini
	$config = parse_ini_file(APP_ROOT.'config/settings.ini');
	$access_token = $config['facebook_access_token'];

	$url = 'https://graph.facebook.com/me/photos';
	
	$params = array(
		'message' => "taken at ".date("g:ia")." in the haunted photo booth",
		'source' => '@'.realpath($file),
		'access_token' => $access_token,
	);
	
	print_r($params);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

	$result = curl_exec($ch);
	curl_close($ch);

	return json_decode($result, 1);
}

