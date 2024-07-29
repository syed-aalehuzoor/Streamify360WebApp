<?php
session_start();

require('dbconfig.php');

if(isset($_POST['login_btn']))
{
    $email_login = $_POST['emaill']; 
    $password_login = $_POST['passwordd']; 

    $query = "SELECT * FROM users WHERE  email='$email_login' AND password='$password_login' LIMIT 1";
    $query_run = mysqli_query($connection, $query);
    $user = mysqli_fetch_array($query_run);

    if($user)
    {
        $_SESSION['userplan'] = $user['userplan'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        header('Location: index.php');
    }
    else
    {
        $_SESSION['status'] = "Email / Password is Invalid";
        header('Location: login.php');
    }
}

if(isset($_POST['logout_btn']))
{
    session_destroy();
    unset($_SESSION['username']);
    unset($_SESSION['email']);
    unset($_SESSION['userplan']);
    header('Location: login.php');
}

if(isset($_POST['registerbtn']))
{
    $username = $_POST['userName'];
    $email = $_POST['inputEmail'];
    $password = $_POST['inputPassword'];
    $cpassword = $_POST['repeatPassword'];
    
    $email_query = "SELECT * FROM users WHERE email='$email' ";
    $email_query_run = mysqli_query($connection, $email_query);
    if(mysqli_num_rows($email_query_run) > 0)
    {
        $_SESSION['status'] = "Email Already Taken. Please Try Another one.";
        header('Location: signup.php');  
    }
    else
    {
        if($password === $cpassword)
        {
            $query = "INSERT INTO users (username,email,password,userplan) VALUES ('$username','$email','$password','basic')";
            $query_run = mysqli_query($connection, $query);
            
            if($query_run)
            {
                // echo "Signuped";
                $_SESSION['userplan'] = 'basic';
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;

                header('Location: index.php');
            }
            else 
            {
                $_SESSION['status'] = "Admin Profile Not Added";
                $_SESSION['status_code'] = "error";
                header('Location: signup.php');  
            }
        }
        else 
        {
            $_SESSION['status'] = "Password and Confirm Password Does Not Match";
            header('Location: signup.php');  
        }
    }

}
?>