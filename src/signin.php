<?php
session_start();

include('../settings/database.php');

$email = $_POST['email'];
$passwd = MD5($_POST['pswd']);

$sql = "
    SELECT 
        id, 
        firstname, 
        lastname,
        email,
        status,
        role
    FROM 
        users 
    WHERE 
        email = '$email' and 
        password = '$passwd' and 
        status = true
    LIMIT 1
";

$res = pg_query($local_conn, $sql);
if (!$res) {
    die("Error in query: " . pg_last_error($local_conn));
}

$row = pg_fetch_assoc($res);
if ($row) {
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['user_firstname'] = $row['firstname'];
    $_SESSION['user_lastname'] = $row['lastname'];
    $_SESSION['role'] = $row['role'] ? $row['role'] : 'user';
    header('refresh:0;url=home.php');
} else {
    echo "<script>alert('Invalid credentials or user is inactive.')</script>";
    header('refresh:0;url=signin.html');
}
?>