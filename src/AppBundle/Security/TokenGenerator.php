<?php


namespace AppBundle\Security;

/**
 * Based on the TokenGenerator class of the FOSUserBundle.
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Util/TokenGenerator.php
 */
class TokenGenerator
{
    public static function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
