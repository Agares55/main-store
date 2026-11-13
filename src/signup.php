<?php
//get datebase connection
include('../settings/database.php');

//get payload

    $first_name = $_POST['fname'];
    $last_name = $_POST['lname'];
    $ident_number = $_POST['idnumber'];
    $mobile_phone = $_POST['mphone'];
    $email = $_POST['email'];
    $passwd = MD5($_POST['pswd']);

//prepare query

$sql="
    INSERT INTO 
    users (firstname, lastname, ident_number, email, password) 
    VALUES (
    '$first_name', 
    '$last_name',
    '$ident_number',
    '$email',
    '$passwd');
";

//Execute query

$res = pg_query($local_conn, $sql);
if ($res){
    echo "User has been created successfully !!!";
} else {
    echo "Error: User hasn't been created successfully !!! Detalle: " . pg_last_error($local_conn);
}