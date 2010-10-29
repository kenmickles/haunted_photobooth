<?php

require 'header.php';

$strips = array();
$files = scandir(PHOTO_PATH, 1);

foreach ( $files as $file ) {
	if ( preg_match('/^combined_(.*)\.jpg$/', $file) ) {
		$strips[] = 'photos/'.$file;
	}
}

// try to keep the page from becoming impossible to load
$strips = array_slice($strips, 0, 50);

?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
		<title>The Haunted Photobooth!</title>
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		<link href='http://fonts.googleapis.com/css?family=IM+Fell+English&amp;subset=latin' rel='stylesheet' type='text/css'>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
		<script type="text/javascript" src="js/jquery.masonry.min.js"></script>
		<script type="text/javascript" src="js/app.js"></script>
		<script type="text/javascript">
			App.init();
		</script>
	</head>
	<body>
		<div id="wrapper">
			<div id="main">
				<h2>Welcome to the Haunted Photo Booth!</h2>
				<a class="take-photo" href="#none"><img src="images/camera_crossbones.png" alt="" /></a>
				<h2 id="status" title="Press any number to begin.">Press any number to begin.</h2>
				<div id="photos"><div class="clr"></div></div>
			</div>			
			<div id="photo-strips">
				<?php foreach ( $strips as $strip ): ?>
					<div class="brick">
						<img src="<?= he($strip) ?>" alt="" />
					</div>
				<?php endforeach; ?>
			</div>
			<div id="flash"></div>
		</div>
	</body>
</html>