<?php

function database($base = "data") {
    $conn = mysqli_connect("localhost", "root", "", "passworld_data"); // Default connections of $base is set to an invalid value
    switch ($base) {
        case 'data':
            //Keep the same
            break;
        case 'passwords':
            $conn = mysqli_connect("localhost", "root", "", "passworld_passwords");
            break;
    }
    return $conn; // Return established connection according to the function called parameters
}

//hashes the main password
function pwdHash($pwd) {
    return hash("sha256", $pwd);
}

//encrypts the password
function pwdEncrypt($pwd) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $ciphertext = openssl_encrypt($pwd, 'aes-256-cbc', "abc123", OPENSSL_RAW_DATA, $iv);
    $result = base64_encode($iv . $ciphertext);

    return $result;
}

//decrypts the password
function pwdDecrypt($pwd) {
    $data = base64_decode($pwd);
    $iv = substr($data, 0, openssl_cipher_iv_length('aes-256-cbc'));
    $ciphertext = substr($data, openssl_cipher_iv_length('aes-256-cbc'));
    $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', "abc123", OPENSSL_RAW_DATA, $iv);

    return $plaintext;
}

//generates a random string of 32 numbers, used to identify a user
function uidGenerate($length = 32) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

//same as uidGenerate but used for passwords
function pidGenerate($length = 16) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

//checks if the username is free
function userExists($usn) {
    $result = database()->query("SELECT * FROM `users` WHERE `username` = '" . $usn . "' LIMIT 1;");
    return mysqli_num_rows($result) < 1;
}

//logs info about user into a table
function registerUser($un, $pwd, $fn, $ln, $mail) {
    $user_id = uidGenerate();
    $hashPwd = pwdHash($pwd);
    database()->query("INSERT INTO `users`(`uid`, `username`, `first_name`, `last_name`, `email`, `password`) VALUES ('$user_id','$un','$fn','$ln','$mail','$hashPwd')");
    registerUserTable($user_id);
}

//creates uid table for storing passwords
function registerUserTable($uid) {
    database("passwords")->query("CREATE TABLE `$uid` (
          `pid` varchar(512) NOT NULL PRIMARY KEY,
          `username` varchar(64) NOT NULL,
          `password` varchar(64) NOT NULL,
          `web` text NOT NULL, 
          `type` varchar(64) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

//deletes all info about a user
function deleteUser($uid) {
    database()->query("DELETE FROM `users` WHERE `uid` = '$uid' LIMIT 1");
    deleteUserTable($uid);
}

//deletes the table
function deleteUserTable($uid) {
    database("passwords")->query("DROP TABLE `$uid`");
}

//adds new password into the uid table
function pwdLog($uid, $un, $pwd, $web, $type) {
    $pid = pidGenerate();
    $pw = pwdEncrypt($pwd);
    database("passwords")->query("INSERT INTO `$uid`(`pid`, `username`, `password`, `web`, `type`) VALUES ('$pid','$un','$pw','$web','$type')");
}

//fetches all info about a password from the uid table
function getPwdInfo($uid, $web) {
    $result = database("passwords")->query("SELECT * FROM `$uid` WHERE `web` = '$web' LIMIT 1");
    return mysqli_fetch_array($result);
}

//same as before but with user data
function getUserInfo($uid) {
    $result = database()->query("SELECT * FROM `users` WHERE `uid` = '$uid' LIMIT 1");
    return mysqli_fetch_array($result);
}

//prints out JSON code
function printJSON($print) {
    echo json_encode($print, JSON_PRETTY_PRINT);
}

