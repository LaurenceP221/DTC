<?php
ob_start();
?>
<?php
require("conn.php");

if(isset($_POST['submit'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $select = mysqli_query($conn, "INSERT INTO adminusers(username, password)
             VALUES ('$username','$password')") or die('query failed');
    
    if($select){
        
        header('Location: admin.html');
        exit();
    }
}
