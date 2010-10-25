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
	
	// combine photos into one file and upload
	if ( $combined_file = combine_photos($files) ) {
		$result = upload_photo($combined_file);
		if ( isset($result['id']) ) {
			$server->send($user->socket, 'photos/'.basename($combined_file));
			echo "Uploaded photo: http://www.facebook.com/photo.php?fbid={$result['id']}\n";
		}
		else {
			echo "Something went wrong:\n";
			print_r($result);
		}
	}
}

function combine_photos($files) {
	// we'll resize the photo to match these
	$resized_width = 640;
	$resized_height = 480;
	$offset = 20;
	
	// calculate image size
	$image_width = $resized_width + ($offset*2);
	$image_height = (count($files) * ($resized_height + $offset)) + $offset;
	
	// create image
	$image = imagecreatetruecolor($image_width, $image_height);
	
	// fill it with a white background
	$white = imagecolorallocate($image, 255, 255, 255);
	imagefilledrectangle($image, 0, 0, $image_width, $image_height, $white);
	
	// add each photo to the image
	foreach ( $files as $i => $file ) {
		// get photo size
		list($full_width, $full_height) = getimagesize($file);
		
		// create gd image resource for this photo
		$photo = imagecreatefromjpeg($file);
		
		// resize the photo and copy it into the main image
		//imagecopyresampled ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
		$dst_y = ($i * $resized_height) + (($i+1) * $offset);
		imagecopyresampled($image, $photo, $offset, $dst_y, 0, 0, $resized_width, $resized_height, $full_width, $full_height);
	}
	
	// output and save image data
	ob_start();
	imagejpeg($image);
	imagedestroy($image);
	$image_data = ob_get_contents();
	ob_end_clean();
	
	// write image to file
	$tmp_file = PHOTO_PATH.'combined_'.time().'.jpg';
	file_put_contents($tmp_file, $image_data);
	
	// and return the file path
	return $tmp_file;
}

function upload_photo($file) {
	//$file = 'photos/manatees.jpg';
	
	// get FB access token from settings.ini
	$config = parse_ini_file(APP_ROOT.'config/settings.ini');
	$access_token = $config['facebook_access_token'];

	$url = 'https://graph.facebook.com/me/photos';
	
	$params = array(
		'message' => "Taken by the Haunted Photo Booth on ".date('F jS, Y')." at ".date('g:ia'),
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

