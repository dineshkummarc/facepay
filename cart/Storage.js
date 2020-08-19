//add new key=>value to the HTML5 storage
function SaveItem() {
			
	var name = document.forms.ShoppingList.name.value;
	var data = document.forms.ShoppingList.data.value;
	localStorage.setItem(name, data);
	if (localStorage.length > 0)
	{
		//Display the checkout button
		document.getElementById('btnCheckout').disabled=false;
	} else {
		document.getElementById('btnCheckout').disabled=true;
	}
	doShowAll();
	
}
//------------------------------------------------------------------------------
//change an existing key=>value in the HTML5 storage
function ModifyItem() {
	var name1 = document.forms.ShoppingList.name.value;
	var data1 = document.forms.ShoppingList.data.value;
	//check if name1 is already exists
	
//check if key exists
			if (localStorage.getItem(name1) !=null)
			{
			  //update
			  localStorage.setItem(name1,data1);
			  document.forms.ShoppingList.data.value = localStorage.getItem(name1);
			}
			if (localStorage.length > 0)
			{
				//Display the checkout button
				document.getElementById('btnCheckout').disabled=false;
			} else {
				document.getElementById('btnCheckout').disabled=true;
			}
		
	
	doShowAll();
}
//-------------------------------------------------------------------------
//delete an existing key=>value from the HTML5 storage
function RemoveItem() {
	var name = document.forms.ShoppingList.name.value;
	document.forms.ShoppingList.data.value = localStorage.removeItem(name);
	if (localStorage.length > 0)
	{
		//Display the checkout button
		document.getElementById('btnCheckout').disabled=false;
	} else {
		document.getElementById('btnCheckout').disabled=true;
	}
	doShowAll();
}
//-------------------------------------------------------------------------------------
//restart the local storage
function ClearAll() {
	localStorage.clear();
	if (localStorage.length > 0)
	{
		//Display the checkout button
		document.getElementById('btnCheckout').disabled=false;
	} else {
		document.getElementById('btnCheckout').disabled=true;
	}

	doShowAll();
}
//--------------------------------------------------------------------------------------
// dynamically populate the table with shopping list items
//below step can be done via PHP and AJAX too. 
function doShowAll() {
	if (CheckBrowser()) {
		var key = "";
		var list = "<tr><th>Item</th><th>Value</th></tr>\n";
		var i = 0;
		//for more advance feature, you can set cap on max items in the cart
		for (i = 0; i <= localStorage.length-1; i++) {
			key = localStorage.key(i);
			list += "<tr><td>" + key + "</td>\n<td>"
					+ localStorage.getItem(key) + "</td></tr>\n";
		}
		//if no item exists in the cart
		if (list == "<tr><th>Item</th><th>Value</th></tr>\n") {
			list += "<tr><td><i>empty</i></td>\n<td><i>empty</i></td></tr>\n";
		}
		//bind the data to html table
		//you can use jQuery too....
		document.getElementById('list').innerHTML = list;
	} else {
		alert('Cannot save shopping list as your browser does not support HTML 5');
	}
}

/*
 =====> Checking the browser support
 //this step may not be required as most of modern browsers do support HTML5
 */
 //below function may be redundant
function CheckBrowser() {
	if ('localStorage' in window && window['localStorage'] !== null) {
		// we can use localStorage object to store data
		return true;
	} else {
			return false;
	}
}
//-------------------------------------------------
/*
You can extend this script by inserting data to database or adding payment processing API to shopping cart..
*/
function SaveToDatabase() {
	var userid = document.getElementById("hidden_user_id").value; 
	console.log("The user id value is " + userid);
	var productName = "";
	var quantity="";
	var i = 0;
	if (localStorage == null) {
		alert("There are no products to save!");
		return;
	}

	if (localStorage.length == 0) {
		alert("There are no products to save.");
		return;
	}
	let rawStorage = JSON.stringify(localStorage);
	console.log(rawStorage);
	//for more advance feature, you can set cap on max items in the cart
	
		productName = localStorage.key(i).replace("&","");
		quantity = localStorage.getItem(productName);
		//Do Ajax Call to PHP script that saves all this data into a table one by one
		let request = new XMLHttpRequest();
		if (!request) {
			alert('Oops. Something went wrong. Please try again or upgrade your browser.');
			return false;
		}
		let url="insertOrder.php?data=" +  encodeURIComponent(rawStorage) + "&user_id="+encodeURIComponent(userid);
		
		console.log(url);
		request.open('GET', url,true);
		request.send();
		localStorage.clear();
}