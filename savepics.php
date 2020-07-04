<?php 
require "settings/database.php";
require "settings/fileupload.php";


//$sql_all_data="insert into tbl_notes(notes) values(:notes)";




/**
 * 
*/
function SaveFilename($userId, $uploadfile)
{
    $status=FALSE;
    require "settings/database.php";
    $sql_store_image="insert into tbl_user_images(userId, imageName) values(:userid, :imageName)";
//    $sql_store_image="insert into tbl_user_images(userId, imageName) values('{$userId}', '{$uploadfile}')"; 
//    print("<br> &gt;&gt; SQL_STORE_IMAGE is: <strong>". $sql_store_image . "</strong><br>");
   try
    {
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
function GetNextTrainingImageName($userid, $fileExtension)
{
    require "settings/database.php";
    $index="1";
     //Count the last index in the table for this user on the table tbl_user_images
     $sql_count_user_images="select count(id)+1 as nextId from tbl_user_images where userId=:userId";
     try
     {
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
   print("<br> Dumping filesArray<br>");
    var_dump($filesArray);
    print("<br>Dumping the destinationFilename variable:<strong>");
    print($destinationFilename);
    print("</strong><br>");
    if (move_uploaded_file($filesArray['webcam']['tmp_name'], $destinationFilename)) {
        echo "File is valid, and was successfully uploaded.\n";
        $upload_status=TRUE;
    } else {
        echo "Possible file upload attack!\n";
        $upload_status=FALSE;
    }
    return $upload_status;
}
//if (isset($_POST['webcam']) or isset($_REQUEST['webcam']))
//{
   
    if (isset($_FILES))
    {
        $destFileExtension = GetExtensionFromFilename($_FILES['webcam']['name']);
        print(">>>File size in bytes is :<strong>". $_FILES['webcam']['size']. "</strong><br>");
        print(">>>File name from form is: <strong>". $_FILES['webcam']['tmp_name']. "</strong><br>");
        print (">>>File Extension of Uploaded File is: <strong>". $destFileExtension. "</strong><br>");
        $destFilename = GetNextTrainingImageName(htmlspecialchars($_GET['userId']) ,  $destFileExtension);
        print (">>>Generated File Name of Destination File is: <strong>". $destFilename . "</strong><br>");
        $uploaddir = "";
        $UPLOAD_DIR = trim($UPLOAD_DIR); //trim whatever was in the "included fileupload.php" file.
        $SUBJECT_FOLDER_NAME_PREFIX = trim($SUBJECT_FOLDER_NAME_PREFIX); //trim whatever comes from the setting files in case of errors.
       
        //Let's the get full upload filepathand name
        $uploadfile=  "";
        $lastCharacter = substr($UPLOAD_DIR, strlen($UPLOAD_DIR)-1, 1);

        print(">>> The imported upload directory is ". $UPLOAD_DIR. "<br>");
        print(">>> Last character in directory is ". $lastCharacter . "<br>");        
        
        if ($lastCharacter === "/" || $lastCharacter === "\\")
        {
            $uploaddir = trim($UPLOAD_DIR . $SUBJECT_FOLDER_NAME_PREFIX . htmlspecialchars($_GET['userId']) . "/");
        }  else 
        {
            $uploaddir = trim($UPLOAD_DIR . "/" . $SUBJECT_FOLDER_NAME_PREFIX . htmlspecialchars($_GET['userId']) . "/");
        }
        try
        {      
            if (!is_dir($uploaddir))
            {
                mkdir($uploaddir, 0777, TRUE);
               print("<br>>>> Just Mkdir() A New Directory:<strong>".$uploaddir."</strong><br>");
            }
        }
        catch(Exception $ex)
        {
            print(">>>Exceptin when creating destination folder. <strong>". $e->getMessage()."</strong><br>");
        }

         $uploadfile =$uploaddir . $destFilename;

        print(">>>Full file name: <strong>". $uploadfile. "</strong><br>");

        $is_uploaded = FALSE;
       $is_uploaded = upload($_FILES, $uploadfile);
// $is_uploaded = upload($_FILES, $_GET["userId"], $uploaddir);
       if ($is_uploaded)
       {
           //Save the info of the file into the database
           print(">>><br>Before SaveFilename()-> userid is: <strong>". htmlspecialchars($_GET['userId'])."</strong><br>");

           SaveFilename(htmlspecialchars($_GET['userId']), $uploadfile);
       }
    }

 //   $status = 100;
//}
// try{
//     $conn = new PDO("mysql:host=$servername;dbname=$dbName", $dbUsername, $dbPassword);
//     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// 	$stmt=$conn->prepare($sql_store_image);
//     $stmt->bindParam(":userId", $userid);
//     $stmt->bindParam(":imageName", $status);
//     $stmt->execute();
//     print ("DEBUG>>> ". $stmt->rowCount(). " rows updated. <br>");
//     print("DEBUG>>> ". isset($_POST));
//     print("FILES array content: <br>". $array_string);
//     $stmt = $conn->prepare($sql_all_data);
//     $stmt->bindParam(":notes", $array_string);
//     $stmt->execute();

//     //print all the elements in the $_POST superglobal array
//     foreach($_POST as $key)
//     {
//         print ($key);
//     }
// }catch(PDOException $e)
// {
//     print("Error in DB Connection/Operations>>> " . $e->getMessage());
// }
?>