<?php

session_start();
include('dbconfig.php');

if($connection)
{
    // echo "Database Connected";
}

if (!(isset($_SESSION['username']))){
    header('Location: login.php');
}

?>