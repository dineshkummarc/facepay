<?php 
session_start();
require "settings/database.php";

//get the user id
$user_id=$_GET['id'];
$sql_get_details="select * from tbl_user where id=$user_id";
$pdo=new PDO("mysql:host=localhost;dbname=db_facepay", "root", "");
$rowset = $pdo->query($sql_get_details);
$row_array = null;
$row_array = getUserTable($rowset, true);
$shopping_list_arr = array();
if (count($row_array) > 0) {
    $shopping_list_arr = getUserShoppingArray($user_id);
}
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
	<h1>All Users</h1>
    <ul>
<li><a href='shop.php'>Shop again</a>
<li><a href='index.php?acton=logout'>Logout</a>
</ul>
    <br>
    <?php 
        if (count($row_array) > 0) {
            foreach($row_array as $row) {
                echo $row;
            }
        } else 
        {
            echo "<b>User detail not found.</b>";
        }
    ?>
</body>
</html>