<?php
include "corefile.php";

//$uid = $_GET['uid'];

$pwd = pwdEncrypt("pepe");
$try = pwdEncrypt("try 2");
pwdDecrypt($try);

//pwdLog("86570620951021069268228344731596", "pepe", $pwd, "panel.themis.eu", "themis acc");
