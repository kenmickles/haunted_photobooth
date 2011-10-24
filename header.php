<?php

error_reporting(E_ALL);
date_default_timezone_set('America/New_York');

define('APP_ROOT', realpath(dirname(__FILE__)).'/');

$config = parse_ini_file(APP_ROOT.'config.ini');
foreach ( $config as $key => $val ) {
  define(strtoupper($key), $val);
}

require APP_ROOT.'includes/functions.php';

session_start();