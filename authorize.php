<?php

/**
 * A super basic implementation of Facebook's auth flow. Use this page to get an access  
 * token for the Facebook user that will be uploading the photos.
 *
 * See http://developers.facebook.com/docs/authentication/ for more info.
 *
 * @author Ken Mickles
 */

require 'header.php';

// tell Facebook to send us right back here
$redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];

// if we've got a code, the user just came back from authorizing the app
// next, we exchange the code for an access token
if ( isset($_GET['code']) ) {
	$url = 'https://graph.facebook.com/oauth/access_token?client_id='.FACEBOOK_APP_ID.'&redirect_uri='.$redirect_uri.'&client_secret='.FACEBOOK_APP_SECRET.'&code='.$_GET['code'];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$result = curl_exec($ch);
	curl_close($ch);
	
	parse_str($result, $data);
	
	if ( isset($data['access_token']) ) {
		// the access token goes in settings.ini
		echo "Your access token is: <strong>{$data['access_token']}</strong>";
	}
	else {
		echo "Something went wrong: <pre>{$result}</pre>";
	}
}
// redirect to the Facebook auth dialog
else {
	header('Location: https://graph.facebook.com/oauth/authorize?client_id='.FACEBOOK_APP_ID.'&redirect_uri='.$redirect_uri.'&scope=publish_stream,offline_access');
}

?>