<?php

require "settings/database.php";
require "settings/trainingsettings.php";
require "settings/menu.php";


if (isset($_GET["action"]) && $_GET["action"] =="logout")
{
	//Todo. Delete all the  authentication images from the file system to keep the system from being full of images
	//whicha re not actively being used.
	//But of course in a production envirionment you would only delete images for the user logging out.
	DeleteAllFiles(/*$_GET["USER_ID"]*/0, true);

	//Then abort the session
	session_abort();
	
}

function DeleteAllFiles($userId, $ignoreUserId=true)
{
	//Read from table auth and loop through all filenames
	//delete each file from the table as well as from the filesystem.
	$sql = "select id, imageName from tbl_user_image_auth_reqs  ";
	if (!$ignoreUserId)
	{
		$sql .= " WHERE userId=:userId";
	}
	try 
	{
	//	require "settings/database.php";
	global $servername, $dbUsername, $dbname, $dbPassword;
		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbUsername, $dbPassword);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt=$conn->prepare($sql);
		if (!$ignoreUserId)
		{
			$stmt->bindParam(":userId", $userId);
		}
		// use exec() because no results are returned
		$stmt->execute();
		$result = $stmt->fetchall();
		if(count($result) > 0)
		{
			foreach ($result as $k)
			{
				unlink($k[1]);
			}
			//Delete all the records in the table
			$conn->exec("delete from tbl_user_image_auth_reqs");


		}				
	}
	catch(PDOException $e) 
	{
		echo $sql . "<br>" . $e->getMessage();
	}
}

$usr = "";
$password ="";
	
	
function test_input($data) 
{
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
}

$hasErrors = false;
$errorMsg=""; //"Sample Error Message";
$isLoggedIn=FALSE;
$hasEnoughPictures = FALSE;
if(isset($_POST['btnSubmit'])) 
{ 
		//print("DEBUG>> Inside submit handler. <br>");
	
	$usr = test_input($_POST['username']);
	$password =test_input($_POST['pwd']);
		


	//Save the user record to the table
	$sql_select_user="select count(*) as is_exists, id as user_id from tbl_user where username=:username and password=:password";
	$sql_count_pictures="select count(*) as numPics from tbl_user_images where userId=:userid";
	try 
	{
		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbUsername, $dbPassword);
		// set the PDO error mode to exception
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt=$conn->prepare($sql_select_user);
		$stmt->bindParam(":username", $usr);
		$stmt->bindParam(":password", $password);
		// use exec() because no results are returned
		$stmt->execute();
		$result = $stmt->fetch();
		if(count($result) > 0)
		{
			if ($result['is_exists'] =="")
			{
			  $isLoggedIn=FALSE;
			} 
			else if ($result['is_exists'] ==1 )
			{
				$isLoggedIn=TRUE;
				session_start(); //started a new session

				$_SESSION["USER_ID"] = $result['user_id'];
				//Check for the number of images they have
				$stmt = $conn->prepare($sql_count_pictures);
				$stmt->bindParam(":userid", $_SESSION["USER_ID"]);
				$stmt->execute();
				$imageResult = $stmt->fetch();
				if (count($imageResult) > 0)
				{
					if ($imageResult["numPics"] === "")
					{
						$hasEnoughPictures=FALSE;
					} else if ($imageResult["numPics"] >= $NUMBER_OF_TRAINING_IMAGES) //This variable $NUMBER_OF_TRAINING_IMAGES is 
																							  //from "trainingsettings.php"
					{
						$hasEnoughPictures=TRUE;
					}
				}
			}
		}				
	}
	catch(PDOException $e) 
	{
		echo $sql . "<br>" . $e->getMessage();
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
	<title>FacePay..::.. Welcome</title>
</head>
<body>
	<h1>Sign In To The System</h1>
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
	<p>Click <a href='register.php'>here to register</a>.</p>
	<!-- A button for taking snaps -->
	 <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
	 <?php
		if(!$isLoggedIn)
		{
	 ?>
		<div class="form-group">
			<label for="username">Username:</label>
			<input type="text" class="form-control" name="username" placeholder="Input username" value="<?php echo $usr; ?>" >
		</div>
		<div class="form-group">
			<label for="pwd">Password:</label>
			<input type="password" class="form-control" name="pwd" placeholder="Input Password" >
		</div>
		
		<div class="form-group">
		<input type="submit" class="btn btn-primary" name="btnSubmit" value="Login" >
		</div>
		<?php
		}
		else if ($isLoggedIn) //if logged in, we already have the user id in a $_SESSION 
		{ 

			?>
			<A HREF="<?php echo 'cart/shop.php'; ?>">Go to shop</A><br>
			<A HREF="all_users.php">View All Users</A><br>
			<?php
			if (!$hasEnoughPictures)
			{	
			?>
				<A HREF="<?php echo "camera.php?userId=" . htmlspecialchars($_SESSION["USER_ID"]); ?>">Enrol Your Face Biometric Data</A><br>
			<?php 
			}
		}
		?>
		
  </form>	

</body>
</html>
