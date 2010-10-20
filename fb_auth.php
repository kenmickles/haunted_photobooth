<?php

$app_id = '105918109476104';
$app_secret = 'b422d771372dd5d0e731192da5d0c5dc';

$event_id = '122596484465523';
$access_token = '105918109476104|b546a7f9d00e28cb103d1ab7-1404309718|3SNsahptUhcj9y9Kq8cpJfDGduM';

$url = 'https://graph.facebook.com/'.$event_id.'/feed';
$params = array(
	'message' => 'A little something for my homies...',
	'picture' => 'http://dl.dropbox.com/u/1599823/blue_button.png',
	'link' => 'http://developers.facebook.com/docs/reference/api/post',
	'caption' => "This photo was taken by the Haunted Photobooth",
	//'privacy' => '{"value": "EVERYONE"}',
	'method' => 'POST',
	'access_token' => $access_token
);

$ch = curl_init();

$opts = array(
	CURLOPT_CONNECTTIMEOUT => 10,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_TIMEOUT => 60,
	CURLOPT_USERAGENT => 'The Haunted Photobooth (dev)',
	CURLOPT_POSTFIELDS => http_build_query($params, null, '&'),
	CURLOPT_URL => $url,
);

curl_setopt_array($ch, $opts);

$result = curl_exec($ch);
curl_close($ch);

echo $result; exit;


$redirect_uri = 'http://192.168.1.109/~ken/haunted_photobooth/fb_auth.php';

if ( isset($_GET['code']) ) {
	$url = 'https://graph.facebook.com/oauth/access_token?client_id='.$app_id.'&redirect_uri='.$redirect_uri.'&client_secret='.$app_secret.'&code='.$_GET['code'];
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	
	$result = curl_exec($ch);
	curl_close($ch);
	
	parse_str($result, $data);
	
	echo "Your access token is: <strong>{$data['access_token']}</strong>";
}
else {
	header('Location: https://graph.facebook.com/oauth/authorize?client_id='.$app_id.'&redirect_uri='.$redirect_uri.'&scope=publish_stream,offline_access');
}

?>