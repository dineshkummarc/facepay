<?php
require "settings/database.php";
require "settings/trainingsettings.php"; 
require "settings/menu.php";
?>
<!doctype html>

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
	<title>FacePay :: Face Payment Failed</title>
<!-- CSS -->
<style>
#my_camera{
 width: 320px;
 height: 240px;
 border: 1px solid black;
}
</style>
</head>
<body>
<?php echo $menuBar; ?>
<div class="alert alert-danger">
  <strong>Success!</strong> Unfortunately, your face was <strong>NOT</strong> recognized and your payment <strong>declined</strong>.<br>
  Click on the link above to enrol your face biometrics.
</div>
</body>
</html>