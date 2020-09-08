<?php 
session_start();
require "../settings/database.php";
$userId=$_SESSION["USER_ID"];
//Retrieve the list of items purchased by this user.
$sql="SELECT * FROM db_facepay.tbl_user";
$con=new PDO("mysql:host=localhost;dbname=db_facepay", "root", "");
$rowset=$con->query($sql);
$sql_orders="SELECT * FROM tbl_order WHERE user_id=:userId";
$stmt = $con->prepare($sql_orders);
$stmt->bindParam(":userId", $userId);
$is_orders_retrieved = $stmt->execute();
$rowset_orders = $stmt->fetchAll();


$html_table_rows_arr = getUserTable($rowset, true);
$html_table_products_arr = getUserShoppingArray($rowset_orders);
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
	<title>FacePay..::.. Purchase Successful</title>
</head>
<body>
<ul>
<li><a href='index.php?action=home'>Go Home</a>
<li><a href='index.php?acton=logout'>Logout</a>
</ul>
	<h1>Goods Purchased By Users</h1>
	<br>
    <?php 

        // print("Value of is_orders_retrieved is ". $is_orders_retrieved);
        // print("<br />");
        // print_r($rowset_orders);
        if (count($html_table_rows_arr) > 0) {
            foreach($html_table_rows_arr as $row) {
                echo $row;
            }
        } else 
        {
            echo "<b>There are no users yet.</b>";
        }
		
		//Print the goods bought
		if (count($html_table_products_arr) > 0) {
            foreach($html_table_products_arr as $row) {
                echo $row;
            }
        } else 
        {
            echo "<b>There are no products yet.</b>";
        }
    ?>


</body>
</html>
