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

function pwdHash($pwd) {
    return hash("sha256", $pwd);
}

function pwdEncrypt($pwd) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $ciphertext = openssl_encrypt($pwd, 'aes-256-cbc', "abc123", OPENSSL_RAW_DATA, $iv);
    $result = base64_encode($iv . $ciphertext);

    return $result;
}

function pwdDecrypt($pwd) {
    $data = base64_decode($pwd);
    $iv = substr($data, 0, openssl_cipher_iv_length('aes-256-cbc'));
    $ciphertext = substr($data, openssl_cipher_iv_length('aes-256-cbc'));
    $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', "abc123", OPENSSL_RAW_DATA, $iv);

    return $plaintext;
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

function pidGenerate($length = 16) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

function userExistDoes($usn) {
    $result = database()->query("SELECT * FROM `users` WHERE `username` = '" . $usn . "' LIMIT 1;");
    return mysqli_num_rows($result) < 1;
}

function registerUser($un, $pwd, $fn, $ln, $mail) {
    $user_id = uidGenerate();
    $hashPwd = pwdHash($pwd);
    database()->query("INSERT INTO `users`(`uid`, `username`, `first_name`, `last_name`, `email`, `password`) VALUES ('$user_id','$un','$fn','$ln','$mail','$hashPwd')");
    registerUserTable($user_id);
}

function registerUserTable($uid) {
    database("passwords")->query("CREATE TABLE `$uid` (
          `pid` varchar(512) NOT NULL PRIMARY KEY,
          `username` varchar(64) NOT NULL,
          `password` varchar(64) NOT NULL,
          `web` text NOT NULL, 
          `type` varchar(64) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

function deleteUser($uid) {
    database()->query("DELETE FROM `users` WHERE `uid` = '$uid' LIMIT 1");
    deleteUserTable($uid);
}

function deleteUserTable($uid) {
    database("passwords")->query("DROP TABLE `$uid`");
}

function pwdLog($uid, $un, $pwd, $web, $type) {
    $pid = pidGenerate();
    database("passwords")->query("INSERT INTO `$uid`(`pid`, `username`, `password`, `web`, `type`) VALUES ('$pid','$un','$pwd','$web','$type')");
}

function getPwdInfo($uid, $web) {
    $result = database("passwords")->query("SELECT * FROM `$uid` WHERE `web` = '$web' LIMIT 1");
    return mysqli_fetch_array($result);
}