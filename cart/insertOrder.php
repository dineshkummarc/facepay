<?php 
// $qty = $_GET['quantity'];
// $product=$_GET['product'];
// $userid = $_GET['user_id'];
$jsonObjectArr = json_decode(urldecode($_GET['data']), true);
$userid = $_GET['user_id'];


if (count($jsonObjectArr)>0) {
    $pdo=new PDO("mysql:host=localhost;dbname=db_facepay", "root", "");

    $statement = $pdo->prepare('INSERT INTO tbl_order(product,quantity,user_id) VALUES(:product,:quantity,:user_id)');
    foreach($jsonObjectArr as $product=>$qty) {
        $statement->bindParam(":product", $product);
        $statement->bindParam(":quantity", $qty);
        $statement->bindParam(":user_id", $userid);
        $statement->execute();
        $order_id = $pdo->lastInsertId();
    }    
}

?>