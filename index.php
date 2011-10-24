<?php

require 'header.php';

$action = array_val($_GET, 'action');

switch ( $action ) {
  case 'take_photo':
  case 'combine_and_upload':
    echo $action($_GET['id']);
    exit;
    
  default:
    $strips = array();
    $files = scandir(PHOTO_PATH, 1);

    foreach ( $files as $file ) {
    	if ( preg_match('/^combined_(.*)\.(jpg|jpeg)$/', $file) ) {
    		$strips[] = 'photos/'.$file;
    	}
    }

    // try to keep the page from becoming impossible to load
    $strips = array_slice($strips, 0, 50);
    
    require APP_ROOT.'themes/'.THEME.'/index.htm';
}

?>