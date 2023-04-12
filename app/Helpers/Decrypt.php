<?php

namespace App\Helpers;

use App\Models\KeycloakAuth;
class Decrypt
{
    public function keycloakAdminPass() // Decrypt Keycloak Admin Password

    {
        $admin = array();

        $auth = KeycloakAuth::first();
        $username = $auth->ADMIN_USERNAME;
        $msg_encrypted_bundle = $auth->PASSWORD;
        $secret = $auth->SECRET;

        $secret = sha1($secret);

        $components = explode(':', $msg_encrypted_bundle);
        $iv = $components[0];
        $salt = hash('sha256', $secret . $components[1]);
        $encrypted_msg = $components[2];

        $decrypted_msg = openssl_decrypt(
            $encrypted_msg,
            'aes-256-cbc',
            $salt,
            null,
            $iv
        );

        if ($decrypted_msg === false) {
            return false;
        }

        $msg = substr($decrypted_msg, 41);

        $admin['username'] = $username;
        $admin['password'] = $decrypted_msg;
        return $admin;
    }

    public function hashingOldPass($oldPwd,$secret) //encrypt user old password
    {
        $iv = substr(sha1(mt_rand()), 0, 16);
        $oldPwd = sha1($oldPwd);

        $salt = sha1(mt_rand());
        $saltWithPassword = hash('sha256', $oldPwd.$salt);

        $encrypted = openssl_encrypt(
        "$data", 'aes-256-cbc', "$saltWithPassword", null, $iv
        );
        $msg_encrypted_bundle = "$iv:$salt:$encrypted";
        return $msg_encrypted_bundle;
    }

    public function compareOldPass($hashedPassword,$plainPassword,$secret) //compare user old password
    {
        $hashedPassword = sha1($hashedPassword);

        $components = explode( ':', $msg_encrypted_bundle );
        $iv            = $components[0];
        $salt          = hash('sha256', $hashedPassword.$components[1]);
        $encrypted_msg = $components[2];

        $decrypted_msg = openssl_decrypt(
        $encrypted_msg, 'aes-256-cbc', $salt, null, $iv
        );

        if ( $decrypted_msg === false )
            return false;

        $msg = substr( $decrypted_msg, 41 );

        if ( $decrypted_msg === $plainPassword )
        return true;

        else return false;
    }
}
