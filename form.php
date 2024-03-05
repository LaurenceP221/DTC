<?php

require('conn.php');

if(isset($_POST['submit'])){

    //declare variables
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $sex = mysqli_real_escape_string($conn, $_POST['sex']);
    $desig = mysqli_real_escape_string($conn, $_POST['desig']);
    $affil = mysqli_real_escape_string($conn, $_POST['affil']);
    $mobileNum = mysqli_real_escape_string($conn, $_POST['mobileNum']);
    $emailAdd = mysqli_real_escape_string($conn, $_POST['emailAdd']);
    $visiting = mysqli_real_escape_string($conn, $_POST['visiting']);

    //define local path to upload 
    $folderPath = "upload/";
    $image_parts = explode(";base64,", $_POST['signature']);
    $image_type_aux = explode("image/", $image_parts[0]);

    $image_type = $image_type_aux[1];

    $image_base64 = base64_decode($image_parts[1]);

    $file = $folderPath . $name . "_" . uniqid() . '.' . $image_type;

    file_put_contents($file, $image_base64);

    $select = mysqli_query($conn, "INSERT INTO visitors(name, sex, desig, affil, mobileNum, 
                            emailAdd, visiting, `time`, `sign`) VALUES ('$name','$sex', '$desig', '$affil', 
                            '$mobileNum','$emailAdd', '$visiting', CURRENT_TIMESTAMP, '$file')") or die('query failed');

    echo "Signature Uploaded Successfully.";}?>
    <br/><h1>
        <a href="mainForm.html">Create a new entry</a>
    </h1>
    

