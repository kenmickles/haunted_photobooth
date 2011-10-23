<?php

session_start();

error_reporting(E_ALL);
date_default_timezone_set('America/New_York');

define('APP_ROOT', realpath(dirname(__FILE__)).'/');
define('PHOTO_PATH', APP_ROOT.'photos/');
//define('CAPTURE_CMD', '/usr/local/bin/imagesnap');
define('CAPTURE_CMD', 'cp /Users/ken/Sites/haunted_photobooth/photos/1288379027_1.jpg');

function array_val($r, $key, $default=null) {
  return isset($r[$key]) ? $r[$key] : $default;
}

function he($str) {
	return htmlentities($str, ENT_COMPAT, 'UTF-8');
}

function pr($var, $return=FALSE) {
	$pre = '<pre>'.print_r($var, 1).'</pre>';
	if ( $return ) {
		return $pre;
	}
	else {
		echo $pre;
	}
}

function take_photo($id) {
	if ( !isset($_SESSION['photo_number_'.$id]) ) {
	  $_SESSION['photo_number_'.$id] = 1;
	}
	else {
	  $_SESSION['photo_number_'.$id]++;
	}
	
	if ( !isset($_SESSION['spookified_'.$id]) ) {
	  $_SESSION['spookified_'.$id] = false;
	}
	
	$photo_file = $id.'_'.$_SESSION['photo_number_'.$id].'.jpg';
	system(CAPTURE_CMD . ' ' . PHOTO_PATH . $photo_file);
		
	// 1 in 4 chance of getting a ghost
	if ( !$_SESSION['spookified_'.$id] && rand(1, 4) == 3 ) {
		spookify(PHOTO_PATH . $photo_file);
		$_SESSION['spookified_'.$id] = true;
	}
		
	echo json_encode(array(
	 'photo_src' => 'photos/'.$photo_file,
	));
}

function combine_and_upload($id) {
  $files = array();
  $photo_count = $_SESSION['photo_number_'.$id];
  
  for ( $i = 1; $i <= $photo_count; $i++ ) {
    $files[] = PHOTO_PATH . $id . '_' . $i . '.jpg';
  }
  
  // combine photos into one file and upload
	if ( $combined_file = combine_photos($files) ) {
		$result = upload_photo($combined_file);

    if ( !isset($result['id']) ) {
      error_log('Failed to upload to Facebook: '.print_r($result, 1));
    }
		
		// send the combined file back to the client regardless of success
		echo json_encode(array(
		  'photo_src' => 'photos/'.basename($combined_file),
		  'facebook_id' => array_val($result, 'id'),
		));
		// "Uploaded photo: http://www.facebook.com/photo.php?fbid={$result['id']}\n";
	}
}

function spookify($file) {
	// we'll resize the photo to match these
	$resized_width = 640;
	$resized_height = 480;
	
	// get photo size
	list($full_width, $full_height) = getimagesize($file);
	
	// create gd image resource for this photo
	$image = imagecreatefromjpeg($file);
	$resized_image = imagecreatetruecolor($resized_width, $resized_height);
	
	// first we resize the original image
	imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $resized_width, $resized_height, $full_width, $full_height);
	
	// get a random ghost image
	$ghost_file = get_random_ghost();
	
	// merge ghost into the main photo
	list($ghost_width, $ghost_height) = getimagesize($ghost_file);
	$ghost = imagecreatefrompng($ghost_file);
	imagecopyresampled($resized_image, $ghost, 0, 0, 0, 0, $resized_width, $resized_height, $ghost_width, $ghost_height);
	
	// output and save image data
	ob_start();
	imagejpeg($resized_image);
	imagedestroy($resized_image);
	$image_data = ob_get_contents();
	ob_end_clean();
	
	// write image to file
	file_put_contents($file, $image_data);
}

function get_random_ghost() {
	// this is where the ghosts lurk
	$ghost_dir = 'images/ghosts/';
	
	// build a list of available ghosts
	$ghosts = array();
	foreach ( scandir($ghost_dir) as $file ) {
		if ( preg_match('/\.png$/', $file) ) {
			$ghosts[] = $ghost_dir.$file;
		}
	}
	
	// pick a random ghost file
	return $ghosts[array_rand($ghosts)];
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
	// get FB access token from settings.ini
	$config = parse_ini_file(APP_ROOT.'config/settings.ini');
	$access_token = $config['facebook_access_token'];

	$url = 'https://graph.facebook.com/me/photos';
	
	$params = array(
		'message' => "Taken by the Haunted Photo Booth on ".date('F jS, Y')." at ".date('g:ia'),
		'source' => '@'.realpath($file),
		'access_token' => $access_token,
	);

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

?>