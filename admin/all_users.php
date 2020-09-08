<?php
//Connect to db and SELECT * FROM users
session_start();
require "../settings/database.php";

$sql="SELECT * FROM db_facepay.tbl_user";
$con=new PDO("mysql:host=localhost;dbname=db_facepay", "root", "");
$rowset=$con->query($sql); 


$html_table_rows_arr = getUserTable($rowset);


//Render it in bootstrap responsive table with links with ID as a GET url variable

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
	<title>FacePay..::.. Welcome</title>
</head>
<body>
<ul>
<li><a href='index.php?action=home'>Go Home</a>
<li><a href='index.php?acton=logout'>Logout</a>
</ul>
	<h1>All Users</h1>
    <br>
    <?php 
        if (count($html_table_rows_arr) > 0) {
            foreach($html_table_rows_arr as $row) {
                echo $row;
            }
        } else 
        {
            echo "<b>There are no users yet.</b>";
        }
    ?>
</body>
</html>