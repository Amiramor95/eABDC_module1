<?php

$copy = copy("./JWT.php","./vendor/firebase/php-jwt/src/JWT.php");
$copy = copy("./ThrottlesLogins.php","./vendor/laravel/framework/src/Illuminate/Auth/ThrottlesLogins.php");

// $copy = copy("./KeycloakGuard.php","./vendor/robsontenorio/laravel-keycloak-guard/src/KeycloakGuard.php"); 

// $copy = copy("./config/keycloakAdmin.php","./vendor/haizad/laravel-keycloak-admin/src/Config/keycloakAdmin.php"); 
// Read the whole file into an array
// $x = 1;
// while($x <= 3) {

//     $file_out = file("./vendor/firebase/php-jwt/src/JWT.php"); // Read the whole file into an array

//     unset($file_out[136]);

//     file_put_contents("vendor/firebase/php-jwt/src/JWT.php", implode("", $file_out));
//     $x++;
// }
?>