<?php
require "settings/database.php";
require "settings/menu.php";

	/**
	 * This function validates the input
	 * @return a string. If it is Empty, that means that the validation was successful. Otherwise, it is non-empty. Means there were errors
	 * 
	*/
	$usr = "";
		$password ="";
		$pwdConfirm = "";
		$cardName="";
		$cardNumber="";
		$cvv = "";
		$cardPin="";
		$cardPinConfirm = "";
	function validateInput($usr, $password, $pwdConfirm,$cardName, $cardNumber, $cvv, $cardPin, $cardPinConfirm)
	{
		$validationErrStr="";
		if (trim($usr)=="")
		{
			return "The username cannot be empty";			
		}
		if (trim($password)=="" || trim($pwdConfirm)=="")
		{
			return "The password and the confirmed password cannot be empty";			
		}

		if (trim($password) !=trim($pwdConfirm))
		{
			return "Please repeat the password again to confirm it. The 2 have to be the same.";			
		}
		
		if (trim($cvv)=="" )
		{
			return "The CVV should not be empty.";			
		}

		if (strlen(trim($cvv))!=3 )
		{
			return "The CVV should be 3 digits long.";			
		}

		if (!is_numeric($cvv))
		{
			return "The CVV should be numeric.";
		}
		
		if (trim($cardName)=="" )
		{
			return "The card name cannot be empty";			
		}

		if (trim($cardNumber)=="")
		{
			return "Please enter the card number";
		}
		$cardNumberLen=strlen(trim($cardNumber));
		if ($cardNumberLen < 12 || $cardNumberLen > 15) //Lenght 12 for Visa Cards and greater than 15 for Verve
		{
			return "The card number should be between 12 and 15 characters long.";
		}

		return $validationErrStr;
	}

	function test_input($data) 
	{
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	$hasErrors = false;
	$errorMsg=""; //"Sample Error Message";
	$isSubmitted = false;

	if(isset($_POST['btnSubmit'])) 
	{ 
		//print("DEBUG>> Inside submit handler. <br>");
	
		$usr = test_input($_POST['username']);
		$password =test_input($_POST['pwd']);
		$pwdConfirm = test_input($_POST['pwdConfirm']);
		$cardName=test_input($_POST['cardName']);
		$cardNumber=test_input($_POST['cardNumber']);
		$cvv = test_input($_POST['cvv']);
		$cardPin=test_input($_POST['cardPin']);
		$cardPinConfirm = test_input($_POST['cardPinConfirm']);

		$validationOutputMessage = validateInput($usr, $password, $pwdConfirm,$cardName, $cardNumber, $cvv, $cardPin, $cardPinConfirm);

		//print("DEBUG>> validateInput() Returned <br>");


		if ($validationOutputMessage != "")
		{
		//	print("DEBUG>> validateInput() ERRORS {$validationOutputMessage} <br>");
			$hasErrors=true;
			$errorMsg=$validationOutputMessage;
			
		} else
		{
				//Save the user record to the table
			$sql_save_user="INSERT INTO tbl_user( username, password, cardName, cardNumber, cardPin, cardCvv, status) ";
			$sql_save_user .= "VALUES (:username, :password, :cardName, :cardNumber, :cardPin, :cardCvv, 0)";
		
			try {
				$conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbUsername, $dbPassword);
				// set the PDO error mode to exception
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$stmt=$conn->prepare($sql_save_user);
				$stmt->bindParam(":username", $usr);
				$stmt->bindParam(":password", $password);
				$stmt->bindParam(":cardName", $cardName);
				$stmt->bindParam(":cardNumber", $cardNumber);
				$stmt->bindParam(":cardCvv",$cvv);
				$stmt->bindParam(":cardPin", $cardPin);
				// use exec() because no results are returned
				$stmt->execute();

				$isSubmitted = true;
				//get the id of the last inserted record to create a link to add the images.
				$userId = $conn->lastInsertId();

				$hasErrors=false;
					//If all is well, then include link to setup face recognition
			$errorMsg=<<<SUCCESS
	<div class="alert alert-success">
	New record created successfully
	</div>
	<br />
	<a href="camera.php?userId=$userId">Enrol Your Face Here</a>
SUCCESS;
			} catch(PDOException $e) {
				echo $sql . "<br>" . $e->getMessage();
			}
		}		
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
	<title>FacePay..::.. Enrol Card</title>
</head>
<body>
<?php echo $menuBar; ?>
	<h1>FacePay Card Enrolment</h1>
	<br>
	
<?php
if ($hasErrors)
{
$errorAlert=<<<ERROR
<div class="alert alert-danger">
  <strong>Warning!</strong> {$errorMsg}.
</div>
ERROR;
echo $errorAlert;
}	
?>

<?php 
	 	echo "&nbsp;&nbsp;" . $errorMsg; 
	 
	 ?> 
	
	<!-- A button for taking snaps -->
	 <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
	 <div class="form-group">
		<label for="username">Enter Your Username:</label>
		<input type="text" class="form-control" name="username" placeholder="Input username" value="<?php echo $usr; ?>" >
	 </div>
	 <div class="form-group">
		<label for="pwd">Enter Your Password:</label>
		<input type="password" class="form-control" name="pwd" placeholder="Input Password" >
	 </div>
	 <div class="form-group">
		<label for="pwdConfirm">Confirm Your Password:</label>
		<input type="password" class="form-control" name="pwdConfirm" placeholder="Confirm password" >
	 </div>
	 <div class="form-group">
		<label for="cardName">Enter Your Card Name:</label>
		<input type="text" class="form-control" name="cardName" placeholder="Input Card Name Here" value="<?php echo $cardName; ?>"  >
	 </div>
	 <div class="form-group">
		<label for="cardNumber">Enter Your Card Number:</label>
		<input type="text" class="form-control" name="cardNumber" placeholder="Input Card Number Here" value="<?php echo $cardNumber; ?>" >
	 </div>
	 <div class="form-group">
		<label for="">CVV(3-digit number):</label>
		<input type="text" class="form-control" name="cvv" maxlength="3">
	 </div>
	<div class="form-group">
		<label for="cardPin">Enter Card PIN:</label>
		<input type="password" name="cardPin" class="form-control"  maxlength="4">
	</div>
	<div class="form-group">
		<label for="cardPinConfirm">Confirm Card PIN:</label>
		<input type="password" name="cardPinConfirm" class="form-control"  maxlength="4">	
	</div>
	<div class="form-group">
	 <!-- <input type="submit" class="btn btn-primary"> -->
	 <?php 
	 if ($isSubmitted==false) 
	 {
	?>
	 <input type="submit" class="btn btn-primary" name="btnSubmit" >
	<?php
	 } 		 
	?> 
	
	</div>
  </form>	
</body>
</html>
