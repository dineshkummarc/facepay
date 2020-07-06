<?php
require "settings/database.php";
require "settings/trainingsettings.php"; 
require "settings/menu.php";

 $passwordErr="";
 $showCameraButtons=TRUE;

    $cardName = "";
    $cardNumber=""; 

    $sql_get_user="SELECT * FROM tbl_user WHERE id=:userId";
    $sql_count_images="select count(*) as numImages from tbl_user_images where userId=:userId";
    
    if (isset($_POST['btnDeleteFaces']))
    {
        //Get the userId in a hidden form field
        $userid=$_POST["hidden_user_id"];
        $sql_delete_all_images="delete from tbl_user_images where userId=:userId";
        $sql_select_files_for_user="select imageName from tbl_user_images where userId=:userId";

        try{
            $conn=new PDO("mysql:host=$servername;dbname=$dbname", $dbUsername, $dbPassword);

            $stmt=$conn->prepare($sql_select_files_for_user);
            $stmt->bindParam(":userId", $userid);
            $stmt->execute();
            $result = $stmt->fetch();
            if (count($result) > 0)
            {
                $filenameArray = array();
                foreach($result as $row)
                {
                    $filenameArray[] = $row["imageName"];
                }
                //Now use a function defined  in the include file "fileupload.php" called "deletePhysicalFiles"
                //Then delete from the folder path
                deletePhysicalFiles($filenameArray);

                //Then delete from the database table
                $stmt = $conn->prepare($sql_delete_all_images);
                $stmt->bindParam(":userId", $userid);
                $stmt->execute();
            }
           

        }catch(PDOException $e)
        {

        }
    }
	if(isset($_GET['userId']) )
	{ 
        $userid = $_GET['userId'];
        
        try 
        {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbUsername, $dbPassword);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
	<title>FacePay :: Enrol Your Face</title>
<!-- CSS -->
<style>
#my_camera{
 width: 320px;
 height: 240px;
 border: 1px solid black;
}
</style>
</head>
<body>
<?php echo $menuBar; ?>
<!-- -->
 <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

    <div class="form-group">
            Welcome, <strong><?php echo $cardName; ?>.<br></strong>
            Card Number: <strong><?php echo $cardNumber; ?></strong><br>
            <strong>important</strong> Please center your face in the camera. Take 12 pictures of your face. Click "Start Camera" to begin.
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
        <input type="button" value="Start Camera" onClick="configure()" class="btn btn-primary" >
        <input type="button" value="Take Snapshot" onClick="take_snapshot()" class="btn btn-primary" >
        <input type="button" value="Save Snapshot" onClick="saveSnap()" class="btn btn-primary" >
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
    <div id="results" ></div>
  </form>
 <!-- Script -->
 <script type="text/javascript" src="./js/webcam.min.js"></script>

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
 }
 // A button for taking snaps


 // preload shutter audio clip
 var shutter = new Audio();
 shutter.autoplay = false;
 shutter.src = navigator.userAgent.match(/Firefox/) ? 'shutter.ogg' : 'shutter.mp3';

 function take_snapshot() {
        // play sound effect
        shutter.play();

        // take snapshot and get image data
        Webcam.snap( 
            function(data_uri) {
                // display results in page
                document.getElementById('results').innerHTML = 
                '<img id="imageprev" src="'+data_uri+'"/>';
            } 
        );

        Webcam.reset();
 }

function saveSnap(){
 // Get base64 value from <img id='imageprev'> source
 var base64image = document.getElementById("imageprev").src;

 Webcam.upload( base64image, 
                "savepics.php?action=training_path&userId=<?php echo htmlspecialchars($_GET['userId']); ?>", 
                function(code, text) {
                    console.log('Code is: ' + code + '\ntext is: ' + text);                
                }
                );


                
}
</script>
</body>
</html>