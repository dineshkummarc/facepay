<?php
//FILE PATHS
$UPLOAD_DIR = "C:/xampp/htdocs/facepay/all_upload/training/";
$TESTDATA_DIR="C:/xampp/htdocs/facepay/all_upload/testdata/";

//$UPLOAD_DIR = "C:/xampp/all_uploads/facepay/training/";
$SUBJECT_FOLDER_NAME_PREFIX = "s"; // This will be used in creating a folder for each user. So user id "1" will have folder named "s1" where "s" is this prefix


function deletePhysicalFiles($fileArray)
{
    //Delete files
    foreach($fileArray as   $filename)
    {
        unlink($filename);
    }
}
?>