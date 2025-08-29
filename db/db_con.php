<?php
$host = 'localhost'; 
$db_name = 'EcoLeaf_db';
$user = 'root'; 
$pass = 'Osbase12*';

$con = mysqli_connect($host, $user, $pass, $db_name);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}