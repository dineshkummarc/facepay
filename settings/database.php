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

function getUserShoppingArray($orders_rowset) {
	$disable_actions=FALSE;
	$html_table_rows_arr = array();    
    if (count($orders_rowset) > 0) {
		if (!$disable_actions) {
			$html_table_rows_arr[] =<<<TABLE_DEF
<div class='container'>
	<table class='table table-hover'>
		<thead>
			<tr>
				<th>Description</th>
				<th>Quantity</th>
				<th>Amount</th>
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
				<th>Quantity</th>
				<th>Amount</th>
			</tr>
		</thead>
		<tbody>
TABLE_DEF;
		}
        
        foreach($orders_rowset as $row)
        {
			$name=$row['product'];
			$qty= $row['quantity'];
			$id=$row['id'];
			$amount = $id * $qty;
			if (!$disable_actions) {
				$html_table_rows_arr[] = "<tr><td>$name</td><td>$qty</td><td>$amount</td></tr>";
			} else {
				$html_table_rows_arr[] = "<tr><td>$name</td><td>$qty</td><td>$amount</td></tr>";
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
?>