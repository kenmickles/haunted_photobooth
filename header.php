<?php

error_reporting(E_ALL);
date_default_timezone_set('America/New_York');

define('APP_ROOT', realpath(dirname(__FILE__)).'/');
define('PHOTO_PATH', APP_ROOT.'photos/');
define('CAPTURE_CMD', '/Users/ken/bin/imagesnap');

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

?>