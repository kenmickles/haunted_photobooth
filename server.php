#!/php -q
<?php  /*  >php -q server.php  */

require 'header.php';
require_once 'includes/WebSocketServer.php';

// set daemon-friendly PHP options
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

// new WebSocketServer( socket address, socket port, callback function )
$webSocket = new WebSocketServer('127.0.0.1', 8080, 'capture');
$webSocket->run();

function capture($user, $count, $server) {
	$photo_path = PHOTO_PATH;
	$capture_cmd = CAPTURE_CMD;
	
	$base_name = time();
	
	for ( $i = 1; $i <= $count; $i++ ) {
		$server->send($user->socket, 'flash');
		
		$photo_file = $base_name.'_'.$i.'.jpg';
		system($capture_cmd.' '.$photo_path.$photo_file);
		
		// TODO: photoboothify and spookify photos
		
		$server->send($user->socket, 'photos/'.$photo_file);
	}
	
	// TODO: combine photos into one file and upload_photo
}

function upload_photo($file) {
	// get FB access token from settings.ini
	$config = parse_ini_file(APP_ROOT.'config/settings.ini');
	$access_token = $config['facebook_access_token'];

	$url = 'https://graph.facebook.com/me/photos';
	
	$params = array(
		'message' => "taken at ".date("g:ia")." in the haunted photo booth",
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

	echo json_decode($result, 1);
}

