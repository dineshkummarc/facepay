<?php
require "../settings/database.php";
require "../settings/menu.php";

	/**
	 * This function validates the input
	 * @return a string. If it is Empty, that means that the validation was successful. Otherwise, it is non-empty. Means there were errors
	 * 
	*/
	$usr = "";
		$password ="";
		$pwdConfirm = "";
		$surname="";
		$firstname="";
		$cvv = "";
		$cardPin="";
		$cardPinConfirm = "";
	function validateInput($usr, $password, $pwdConfirm,$surname, $firstname)
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
		
		if (trim($surname)=="" )
		{
			return "The surname should not be empty.";			
		}

	
		
		if (trim($firstname)=="" )
		{
			return "The first name cannot be empty";			
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
		$surname=test_input($_POST['surname']);
		$firstname=test_input($_POST['firstname']);

		$validationOutputMessage = validateInput($usr, $password, $pwdConfirm,$surname, $firstname);



		if ($validationOutputMessage != "")
		{
			$hasErrors=true;
			$errorMsg=$validationOutputMessage;
			
		} else
		{
				//Save the user record to the table
			$sql_save_user="INSERT INTO tbl_admin( username, password, surname, firstname) ";
			$sql_save_user .= "VALUES (:username, :password, :surname, :firstname)";
		
			try {
				$conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbUsername, $dbPassword);
				// set the PDO error mode to exception
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$stmt=$conn->prepare($sql_save_user);
				$stmt->bindParam(":username", $usr);
				$stmt->bindParam(":password", $password);
				$stmt->bindParam(":surname", $surname);
				$stmt->bindParam(":firstname", $firstname);
				$stmt->execute();

				$isSubmitted = true;
				//get the id of the last inserted record to create a link to add the images.
				$userId = $conn->lastInsertId();

				$hasErrors=false;
					//If all is well, then include link to setup face recognition
			$errorMsg=<<<SUCCESS
	<div class="alert alert-success">
	New Admin created successfully
	</div>
	<br />	
SUCCESS;
			} catch(PDOException $e) {
				echo $sql_save_user . "<br>" . $e->getMessage();
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
<?php echo $menuBarAdmin; ?>
	<h1>Register As An Admin</h1>
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
		<label for="surname">Surname:</label>
		<input type="text" class="form-control" name="surname" placeholder="Surname" value="<?php echo $surname; ?>"  >
	 </div>
	 <div class="form-group">
		<label for="firstname">First name:</label>
		<input type="text" class="form-control" name="firstname" placeholder="Firstname" value="<?php echo $firstname; ?>" >
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
