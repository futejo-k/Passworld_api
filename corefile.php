<?php

function database($base = "data") {
    $conn = mysqli_connect("localhost", "root", "", "passworld_data"); // Default connections of $base is set to an invalid value
    switch ($base) {
        case 'data':
            //Keep the same
            break;
    }
    return $conn; // Return established connection according to the function called parameters
}

function pwdHash($pwd) {
    return hash("sha256", $pwd);
}

function uidGenerate($length = 32) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

function registerUser($un, $pwd, $fn, $ln, $mail)
{
    $user_id = uidGenerate();
    database()->query("INSERT INTO `users`(`uid`, `username`, `first_name`, `last_name`, `email`, `password`) VALUES ('$user_id','$un','$fn','$ln','$mail','$pwd')");
}