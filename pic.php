<?php 
echo "Hello ";
$target_dir = "all_upload/training/s46/";
if (!is_dir($target_dir))
{
    mkdir($target_dir, 0777);
}
$target_file = $target_dir . "test2.jpg";//basename($_FILES["webcam"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
if(isset($_FILES["webcam"])) {
  $check = getimagesize($_FILES["webcam"]["tmp_name"]);
  if($check !== false) {
    echo "File is an image - " . $check["mime"] . ".";
    $uploadOk = 1;
  } else {
    echo "File is not an image.";
    $uploadOk = 0;
  }

  if (move_uploaded_file($_FILES["webcam"]["tmp_name"], $target_file)) {
    echo "The file ". basename( $_FILES["webcam"]["name"]). " has been uploaded.";
  } else {
    echo "Sorry, there was an error uploading your file.";
  }

}
?>