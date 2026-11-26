<?php
/*
Use this Endpoint to test request
http://localhost/main-store/src/delete_user.php?userID=ANY-ID-NUMBER
*/
session_start();
if (!isset($_SESSION['user_id'])) {
    header('refresh:0;url=signin.html');
    exit();
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
if ($role !== 'admin') {
    header('refresh:0;url=home.php');
    exit();
}

include('../settings/database.php');
 
//Get data
$user_id = $_GET['userID'];
 
//Prepare query
$sql_delete_user = "DELETE FROM users WHERE id = $user_id";
$res = pg_query($local_conn, $sql_delete_user);
 
if($res){
    echo "<script>alert('User has been deleted successfully !!!')</script>";
    header('refresh:0;url=list_users.php');
}else{
    echo "<script>alert('User has not been deleted successfully !!!')</script>";
    header('refresh:0;url=list_users.php');
}