<html>
<head>
	<link type='text/css' rel='stylesheet' href='css/index.css'>
</head>
<body>
<?php
error_reporting(0);

require_once('model/application.php');
$Application = new Application;
echo $Application->auth();
?>
</body>
</html>