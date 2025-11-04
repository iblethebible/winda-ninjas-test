<?php
//enter password to be hashed below
$password = '0000';
echo  password_hash($password, PASSWORD_DEFAULT);
?>