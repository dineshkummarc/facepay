<?php
session_start();
require "../settings/database.php";
require "../settings/trainingsettings.php"; 
require "../settings/menu.php";

$sql_get_user      = "SELECT * FROM tbl_user WHERE id=:userId";
$sql_count_images  = "SELECT count(*) as numImages from tbl_user_images where userId=:userId";
$is_form_submitted = 0;
$cardName          = "";
$cardNumber        = ""; 
$showCameraButtons = FALSE;
$delete_Recognitions = "DELETE from tbl_user_image_auth_reqs WHERE userId=:userId";//.htmlspecialchars($_SESSION['USER_ID']);

	if(isset($_POST["hidden_user_id"]) )
	{ 
        $userid = $_POST["hidden_user_id"];
        
        try 
        {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbUsername, $dbPassword);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
            $stmt=$conn->prepare($delete_Recognitions);
            $stmt->bindParam(":userId", $userid);
            $stmt->execute();
			$stmt=$conn->prepare($sql_get_user);
            $stmt->bindParam(":userId", $userid);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (count($result) > 0)
            {
                $cardName = $result["cardName"];
                $cardNumber = $result["cardNumber"];
            }

            $stmt=$conn->prepare($sql_count_images);
            $stmt->bindParam(":userId", $userid);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $currentNumImages = $result["numImages"];
            if ($currentNumImages == "")
            {
                $currentNumImages=0;

            }
            if ($currentNumImages >= $NUMBER_OF_TRAINING_IMAGES)
            {
                //Hid the start camera button. 
                $showCameraButtons=FALSE;
            } else 
            {
                $showCameraButtons=TRUE;
            }
        }
        catch(PDOException $e)
        {
            echo $sql_get_user . "<br>" . $e->getMessage();
        }
		
    } 
    else if (isset($_GET["userId"]) && isset($_GET["action"]))//if both the user id and the action query params are set. Fromthe javascript.
    {
       //print("<strong>The upload from Java Script valuable is  on</strong>");
        //UPate the status of the tbl_users 
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
	<title>FacePay :: Shopping Cart Checkout</title>
<!-- CSS -->
<style>
#my_camera{
 width: 320px;
 height: 240px;
 border: 1px solid black;
}
#btnPayWithFace {
    display: none;
}
</style>
</head>
<body>
<?php echo $menuBarShop; ?>
<!-- -->
 <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

    <div class="form-group">
            <h3>Shopping Cart. Checking out....</h3>
            Welcome, <strong><?php echo $cardName; ?>.<br></strong>
            Card Number: <strong><?php echo $cardNumber; ?></strong><br>
            <strong>important</strong> To Pay, please take a picture of your face to authenticate instead of your card.
    </div> 
    &nbsp;<div id="my_camera"></div>

    <!-- <div class="form-group">
            <label for="username">Enter Your Username:</label>
            <input type="text" class="form-control" name="username" placeholder="Input username" value="<?php echo $usr; ?>" >
    </div>     -->
   <br>
   <?php 
   if ($showCameraButtons) 
   {
    ?>
        <table>
            <tr>
                <td>
                    <input type="button" value="Start Camera" onClick="configure()" class="btn btn-primary" >
                </td>
                <td>   
                    <input type="button" value="Pay With Your Face" id="btnPayWithFace" onClick="authenticateFace()" class="btn btn-primary" >
                </td>
            </tr>
        </table>  
     <?php 
   }
   else if (!$showCameraButtons) //Hide camera buttons
   {
    ?>
<input type="submit" value="Delete My Biometric Data" name="btnDeleteFaces" class="btn btn-primary" >
<input type="hidden" name="hidden_user_id" value="<?php echo htmlspecialchars($_GET['userId']); ?>" >
    <?php 
   }
    ?>
    <div id="results" ></div> &nbsp;
    <!-- <div id="faceDetect" ></div> -->
  </form>
 <!-- Script -->
 <script type="text/javascript" src="../js/webcam.min.js"></script>

 <!-- Code to handle taking the snapshot and displaying it locally -->
 <script language="JavaScript">
 
 // Configure a few settings and attach camera
 function configure(){
        Webcam.set({
        width: 320,
        height: 240,
        image_format: 'jpeg',
        jpeg_quality: 100
        });
        Webcam.attach( '#my_camera' );
        showPayWithFace();
 }
 // A button for taking snaps


 // preload shutter audio clip
 var shutter = new Audio();
 shutter.autoplay = false;
 shutter.src = navigator.userAgent.match(/Firefox/) ? '../shutter.mp3' : '../shutter.ogg';

 function take_snapshot() {
        // play sound effect
        shutter.play();

        // take snapshot and get image data
        Webcam.snap( 
            function(data_uri) {
                // display results in page
                // 10-July-2020
               document.getElementById('results').innerHTML = '<img id="imageprev" src="'+data_uri+'"/>';
            } 
        );

        Webcam.reset();
 }

 function authenticateFace() {
      take_snapshot();
      saveSnap();
      hidePayWithFace();
 }

 function hidePayWithFace()
 {
    var x = document.getElementById("btnPayWithFace");
    x.style.display = "none";
 }

 function showPayWithFace()
 {
    var x = document.getElementById("btnPayWithFace");
    x.style.display = "block";
 }

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}
// function saveSnap(){
//  // Get base64 value from <img id='imageprev'> source
//  var base64image = document.getElementById("imageprev").src;

//  Webcam.upload( base64image, 
//                 "../savepics.php?action=test_path&userId=<?php echo htmlspecialchars($_SESSION["USER_ID"]); ?>", 
//                 function(code, text) {
//                     console.log('Code is: ' + code + '\ntext is: ' + text);  
//                     document.getElementById("imageprev").src = text;//10-July-2020 Set the new filename 
             
//                 }
//                 );
// }



function saveSnap(){
 // Get base64 value from <img id='imageprev'> source
 var base64image = document.getElementById("imageprev").src;

 Webcam.upload( base64image, 
                "../savepics.php?action=test_path&userId=<?php echo htmlspecialchars($_SESSION["USER_ID"]); ?>", 
                function(code, text) {
                    console.log('Code is: ' + code + '\ntext is: ' + text);  
                    //split the text by pipe character
                    var splitArr = text.split('|');
                    var imgUrl = splitArr[0];
                    var successIndicator = splitArr[1];
                    document.getElementById("imageprev").src = imgUrl;//10-July-2020 Set the new filename 
                    if (successIndicator =="3") {
                        document.getElementById('results').innerHTML="<h3><a href='shop.php'>Exceeded Number of Trials. Back to Shop</a></h3><br />"+document.getElementById('results').innerHTML; 
                    } else if (successIndicator =="1") {
                         window.location.href = "success.php";
                    }
                    
                    //wait some time
                    sleep(2000).then(() => { 
                        switch(successIndicator)
                        {
                            case 1:
                                //Then redirect to success page
                                //window.location.href = "success.php?userId=<?php echo htmlspecialchars($_SESSION["USER_ID"]); ?>";
                                window.location.href = "success.php";
                                break;
                            case 2:
                                //try again. do nothing
                                break;
                            case 3: 
                                window.location.href="cart/shop.php";
                                break;
                            default:
                                break;
                        } 
                    });                                    
                   
             
                }
                );
}
</script>
</body>
</html>