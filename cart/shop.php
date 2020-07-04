<?php
require "../settings/menu.php";
?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<META name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<title>FacePay ..::.. Shop Front</title>
<META NAME='rating' CONTENT='General' />
<META NAME='expires' CONTENT='never' />
<META NAME='language' CONTENT='English, EN' />
<META name="description" content="shopping cart project with HTML5 and JavaScript">
<script src="Storage.js"></script>
<link rel="stylesheet" href="StorageStyle.css">
</head>


<body onload="doShowAll()">
<?php echo $menuBar; ?>
	<h2>Facepay Store</h2>
	<p>Insert items and quantity for your shopping cart. </p>
	<form name=ShoppingList>

		<div id="main">
			<table>
				<tr>

					<td><b>Item:</b><div class="form-group"><input type="text" name="name" class="form-control" ></div></td>
					<td><b>Quantity:</b><div class="form-group"><input type="text" class="form-control"  name="data"></div></td>

				</tr>

				<tr>
					<td>
					<div class="form-group">
					    <input type=button value="Save" class="btn btn-primary"   onclick="SaveItem()"> 
					    <input type=button value="Update" class="btn btn-primary"  onclick="ModifyItem()"> 
					    <input type=button value="Delete" class="btn btn-primary"  onclick="RemoveItem()">
					</div>
					</td>
				</tr>
			</table>
		</div>

		<div id="items_table">
			<h3>Shopping Cart</h3>
			<table id=list></table>
			<p>
			<div class="form-group"><label><input type=button value="Clear"   class="btn btn-primary"  onclick="ClearAll()">
					<i>* Delete all items</i></label></div>
			</p>
		</div>
	</form>

</body>
</html>
