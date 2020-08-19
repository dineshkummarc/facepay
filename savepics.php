<?php 
require "settings/database.php";
require "settings/fileupload.php";

/**
 * Do a final action depending on the value of the "action" query string param
*/
function DoFinalAction($action, $userId, $filename)
{
    $jsonResponse ="";
    if ($action=="training_path")
    {}
    
    if ($action =="test_path")
    {        
        $method="GET";
        $url="";
        $url_format="http://127.0.0.1:5000/auth/user/%userId%/%imagefile%";
        $url_format= GetSettingsKeyValue("face_recognition_url_format", "valCol");
        $url_format=str_replace("%userId%", $userId, $url_format);
        $url = $url_format;
       //print(">>>Face recognition url is {$url}<br>");
        
        try {
            $jsonResponse = CallAPI($method, $url, $data = false);
            return $jsonResponse;
        }catch(Exception $e)
        {

        }
        

    }
    return $jsonResponse;
}

// Method: POST, PUT, GET etc
// Data: array("param" => "value") ==> index.php?param=value

function CallAPI($method, $url, $data = false)
{
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
    //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //curl_setopt($curl, CURLOPT_USERPWD, "username:password");

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}

//$sql_all_data="insert into tbl_notes(notes) values(:notes)";
function GetTableNameForAction($action)
{
    $tableName="tbl_user_images";

    switch($action)
    {
        case "training_path":
            $tableName="tbl_user_images";
        break;
        case "test_path":
            $tableName="tbl_user_image_auth_reqs";
        break;
        default:
        $tableName="tbl_user_images";
    break;
    }

    return $tableName;
}
/**
 * Returns ALL rows from the tbl_settings table.
 * @param $keyVal The value of the keyCol column to use in filtering results from the table. 
 * @param $colName The name of the column on the table.
 */
function GetSettingsKeyValue($keyVal,$colName)
{
   // require "settings/database.php";
    $sql_select_settings="select * from tbl_settings where keyCol=?";
   try
    {
        global $servername, $dbUsername, $dbname, $dbPassword;
        $conn= new PDO("mysql:host=$servername;dbname=$dbname", $dbUsername, $dbPassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt=$conn->prepare($sql_select_settings);
        $stmt->bindParam("1", $keyVal);
        $stmt->execute();
        $result = $stmt->fetchAll(/*PDO::FETCH_BOTH*/);//Array (resultset as a whole) of arrays (rows)
       
    }
    catch(PDOException $e)
    {
        print("SQL String: ". $sql_select_settings . "\n" . "Exception Message>>> " . $e->getMessage(). "<br>");        
    }
    //print(var_dump($result) . "<br>");
    return $result[0][$colName];
}

function CountNumberOfRequestsDone($userid)
{
    $sql = "select count(*) as numChecks from tbl_user_image_auth_reqs WHERE userId=?";
   
   try
    {
        global $servername, $dbUsername, $dbname, $dbPassword;
        $conn= new PDO("mysql:host=$servername;dbname=$dbname", $dbUsername, $dbPassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt=$conn->prepare($sql);
        $stmt->bindParam("1", $userid);
        $stmt->execute();
        $result = $stmt->fetchAll(/*PDO::FETCH_BOTH*/);//Array (resultset as a whole) of arrays (rows)
       
    }
    catch(PDOException $e)
    {
        print("SQL String: ". $sql . "\n" . "Exception Message>>> " . $e->getMessage(). "<br>");        
    }
    //print(var_dump($result) . "<br>");
    return $result[0]["numChecks"];
}

/**
 * Add a parameter for table name.
 * This will allow this same method to be used to insert the file details into both table for training images
 * and for test images (used for face recognition/authentication)
 * @param $userId The user id.
 * @param $uploadfile The file name that was uploaded to the folder path.
 * @param $tableName The table name that this file name should be inserted into.
 * @return $status A boolean value. If TRUE, then successfully inserted into the table. FALSE if didnot insert.
*/
function SaveFilename($userId, $uploadfile, $tableName)
{
    $status=FALSE;
   // require "settings/database.php";
    $sql_store_image="insert into ". htmlspecialchars($tableName) ." (userId, imageName) values(:userid, :imageName)";
   try
    {
        global $servername, $dbname, $dbUsername, $dbPassword;
        $conn= new PDO("mysql:host=$servername;dbname=$dbname", $dbUsername, $dbPassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         $stmt = $conn->prepare($sql_store_image);
         $stmt->bindParam(":userid", $userId);
         $stmt->bindParam(":imageName", $uploadfile);
         $stmt->execute();
         $status=TRUE;
    }
    catch(PDOException $e)
    {
        print("SQL String: ". $sql_store_image . "\n" . "Exception Message>>> " . $e->getMessage(). "<br>");
        $status=FALSE;
    }
    return $status;

}
/**
 * Returns the filename that should be created next for a user [id]
*/
function GetNextTrainingImageName($userid, $fileExtension, $tableName)
{
    //require "settings/database.php";
    $index="1";
     //Count the last index in the table for this user on the table tbl_user_images
    //  $sql_count_user_images="select count(id)+1 as nextId from tbl_user_images where userId=:userId";
     $sql_count_user_images="select count(id)+1 as nextId from ". htmlspecialchars($tableName) ." where userId=:userId";
     try
     {
         global $servername, $dbname, $dbUsername, $dbPassword;
         $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbUsername, $dbPassword);
         $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         $stmt = $conn->prepare($sql_count_user_images);
         $stmt->bindParam(":userId", $userid);
         $stmt->execute();
         $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
         if (count($result) > 0)
         {
             $index = $result["nextId"];
             if (strtolower(trim($index))==="null")
             {
                 $index="1";
             } 
             else if (strtolower(trim($index)) ==="")
             {
                 $index="1";
             }
         }        
     }
     catch(PDOException $e)
     {
         print ("SQL String: ". $sql_count_user_images . "\nException message: \n". $e->getMessage(). "<br>");
         return "";
     }    

     return $index.$fileExtension;

}
/**
 * @param $filename - This is the full file name e.g. "mypix.jpg"
 * @return The file name extension. E.g. if the $filename is "mypix.jpg" This function returns ".jpg"
*/
function GetExtensionFromFilename($filename)
{
    return substr($filename, strripos($filename, "."));
}

/**
 * Uploads the file in the $filesArray (which is actually $_FILES global array for file upload from forms) to the training folder path.
 * The destination file name is specified too.
 * @param $filesArray. The $_FILES global array which contains uploads from the form.
 * @param $user_id. The id of the user we are uploading for.
 * @param $destinationFilename The full filename of the destination file.
*/
function upload($filesArray, $destinationFilename)
{
    $upload_status=FALSE;
    if (!isset($filesArray) || !is_array($filesArray)) 
    {
        return $upload_status;
    }
    
//echo $uploadfile;
    //echo '<pre>';
  // print("<br> Dumping filesArray<br>");
  //  var_dump($filesArray);
  //  print("<br>Dumping the destinationFilename variable:<strong>");
 //   print($destinationFilename);
//    print("</strong><br>");
    if (move_uploaded_file($filesArray['webcam']['tmp_name'], $destinationFilename)) {
       // echo "File is valid, and was successfully uploaded.\n";
        $upload_status=TRUE;
    } else {
       //echo "Possible file upload attack!\n";
        $upload_status=FALSE;
    }
    return $upload_status;
}

if (isset($_FILES) && $_GET['action'])
{
    $action = strtolower(trim($_GET['action']));
    $tableName="tbl_user_images";
    $tableName=GetTableNameForAction($action);
   
    $destFileExtension = GetExtensionFromFilename($_FILES['webcam']['name']);
    $destFilename = GetNextTrainingImageName(htmlspecialchars($_GET['userId']) ,  $destFileExtension, $tableName);
    //print (">>>Generated File Name of Destination File is: <strong>". $destFilename . "</strong><br>");
    
    $uploaddir = "";
    if ($action === "training_path")
    {
        $uploaddir = trim($UPLOAD_DIR); //trim whatever was in the "included fileupload.php" file.
    } else if ($action === "test_path")
    {
        $uploaddir = trim($TESTDATA_DIR); //trim whatever was in the "included fileupload.php" file.
    }
    
    $SUBJECT_FOLDER_NAME_PREFIX = trim($SUBJECT_FOLDER_NAME_PREFIX); //trim whatever comes from the setting files in case of errors.
    
    //Let's the get full upload filepathand name
    $uploadfile=  "";
    $lastCharacter = substr($uploaddir, strlen($uploaddir)-1, 1);
	
   // print(">>> The imported upload directory is ". $uploaddir. "<br>");
   // print(">>> Last character in directory is ". $lastCharacter . "<br>");        
    
    if ($lastCharacter === "/" || $lastCharacter === "\\")
    {
        $uploaddir = trim($uploaddir . $SUBJECT_FOLDER_NAME_PREFIX . htmlspecialchars($_GET['userId']) . "/");
    }  else 
    {
        $uploaddir = trim($uploaddir . "/" . $SUBJECT_FOLDER_NAME_PREFIX . htmlspecialchars($_GET['userId']) . "/");
    }
    try
    {      
        if (!is_dir($uploaddir))
        {
            mkdir($uploaddir, 0777, TRUE);
        //   print("<br>>>> Just Mkdir() A New Directory:<strong>".$uploaddir."</strong><br>");
        }
    }
    catch(Exception $ex)
    {
       // print(">>>Exceptin when creating destination folder. <strong>". $e->getMessage()."</strong><br>");
    }
	
     $uploadfile =$uploaddir . $destFilename;
	
   //print(">>>Full file name: <strong>". $uploadfile. "</strong><br>");
	
    $is_uploaded = FALSE;
    $is_uploaded = upload($_FILES, $uploadfile);
    if ($is_uploaded)
    {
        $is_file_saved_to_db = SaveFilename(htmlspecialchars($_GET['userId']), $uploadfile, $tableName);
               
        if ($is_file_saved_to_db )
        {
           $json =  DoFinalAction($action, htmlspecialchars($_GET['userId']), $uploadfile);
           //Treat the response of the json
           if ($json != "")
           {
               $jsonObject = json_decode($json, true);
               //var_dump($jsonObject);
               //Get the filename of the image that has the face bounded by a box.
               if (trim(strtolower($jsonObject["status"])) == "true")
               {
                    print($jsonObject["filename"] . "|" . "1"); //1 means that redirect to the success page.
               } else
               {
                   //Count the number of times that we have already done this check
                   $numFaceRecogsDone = CountNumberOfRequestsDone(htmlspecialchars($_GET['userId']));
                   //get the settings for number of recognitions from the db
                   $settingsNumRecogs = GetSettingsKeyValue("num_recogs", "valCol");
                   if ($numFaceRecogsDone <= $settingsNumRecogs) 
                   {
                       print("../img/face_not_found.png". "|" . "2"); //2 means do nothing
                   } else {
                        print("../img/face_not_found.png". "|" . "3"); //3 means that display Exceeeded trial count message and link to shop  again
                   }
                    
               }
 
               
           } else {//if the json is empty, this means that the action was not train_path
               // print("AUTH@uploaded");
           }
        }
    } else 
    {
        //print("NOT_AUTH@FILE_NOT_UPLOADED");
        return; //TODO: Remove
    }
}
    


?>