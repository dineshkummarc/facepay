<?php
	
	//DATABASE SETTINGS
	$servername="localhost";
	$dbname="db_facepay";
	$dbUsername="root";
	$dbPassword="";
	

	function getUserTable($rowset, $disable_actions=FALSE)
{
    $html_table_rows_arr = array();    
    if (count($rowset) > 0) {
		if (!$disable_actions) {
			$html_table_rows_arr[] =<<<TABLE_DEF
<div class='container'>
	<table class='table table-hover'>
		<thead>
			<tr>
				<th>Description</th>
				<th>Value</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
TABLE_DEF;
		} else {
			$html_table_rows_arr[] = <<<TABLE_DEF
<div class='container'>
	<table class='table table-hover'>
		<thead>
			<tr>
				<th>Description</th>
				<th>Value</th>
			</tr>
		</thead>
		<tbody>
TABLE_DEF;
		}
        
        foreach($rowset as $row)
        {
            $name=$row['cardName'];
			$id=$row['id'];
			if (!$disable_actions) {
				$html_table_rows_arr[] = "<tr><td align='right'>Name:</td><td>$name</td><td><a href='user_detail.php?id=$id'>View</a></td></tr>";
			} else {
				$html_table_rows_arr[] = "<tr><td align='right'>Name:</td><td>$name</td></tr>";
			}
            
        }
		$html_table_rows_arr[] =<<<TABLE_DEF
		</tbody>
	</table>
</div>
TABLE_DEF;
	}
	return $html_table_rows_arr;
}

function getUserShoppingArray($user_id) {
	$list = array();
	$pdo = new PDO("mysql:host=localhost;dbname=db_facepay", 'root', '');
	$statement = $pdo->prepare('select * from tbl_order where user_id=:userid');
	$statement->bindParam(":userid", $user_id);
	$statement->execute();
	$resultset = $statement->fetchAll();
	if (count($resultset) > 0) {
		foreach ($resultset as $row) {
			//
		}
	}
	
}
?>