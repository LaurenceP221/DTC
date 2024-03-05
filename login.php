<?php
ob_start();
?>
<?php
require("conn.php");

if(isset($_POST['submit'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $result = mysqli_query($conn, "SELECT * FROM adminusers WHERE username = '$username' 
                            AND password = '$password'") or die('query failed');
    
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);  
    $count = mysqli_num_rows($result);
    
    if($count == 1){
        header('Location: pdfPrinting.html');
    }else{
        echo "Wrong username and password.";
    }
}