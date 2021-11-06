<?php
namespace Sbe1\Webcore;

/**
 * A class to handle reasonably secure passwords in PHP using the two best out of box options available.
 * This class is not very secure yet probably more secure than 99% of current websites.
 * 
 * Method 1 is using the built in password_hash function using the default bcrypt algorithm.
 * Method 2 is using a truly random salt and a password hash generated by the hash_pbkdf2 function.
 * 
 * Recommended minium password string length: 10
 * Recommended maximum password string length: 255 or unlimited if possible.
 * The user should be able to enter anything they want, including spaces, as long as it meets the length requirements.
 * Recommended hash algorithm for PBKDF2: sha256
 * Recommended hash iterations for PBKDF2: no less than 1000
 * Recommended minimum salt length for PBKDF2: 255
 * 
 * ========================
 * password_hash method
 * ========================
 * 
 *  ==> Steps to create a secure password <==
 * 
 * 1) Take a user provided password stirng to generate a hash with
 * $password_hash = BasicPassword::generatePassword('this is my password');
 * 
 * 2) Save the $password_hash to a database to verify in the future.
 * 
 * ==> Steps to verify the password you created <==
 * 
 * 1) Capture the user's password input (for example from a web form) and store it in the variable $password.
 * 
 * 2) Retrieve the password hash you have stored and assign to the variable $password_hash.
 * 
 * 3) Verify the password with the BasicPassword::verifyPassword($password, $password_hash);
 *  BasicPassword::verifyPassword will return a boolean value that will be true if the password is valid
 *  and false if it is not.
 * 
 * ========================
 * PBKDF2 method
 * ========================
 * 
 * ==> Steps to create a secure password <==
 * 
 * 1) Create a secure salt with $salt = BasicPassword::generateSalt(255);
 * 
 * 2) Generate a new password hash with a password string provided by the user by using
 * $password_hash = BasicPassword::generatePassword('this is my password', $salt);
 * 
 * Save both $salt and $password_hash to a database to reuse and verify this password in the future.
 * 
 * ==> Steps to verify the password you created <==
 * 
 * 1) Capture the user's password input (for example from a web form) and store it in the variable $password.
 * 
 * 2) Retrieve the password hash and salt you have stored and assign to the variables $password_hash and $salt.
 * 
 * 3) Verify the password with the BasicPassword::verifyPassword($password_hash, $password, $salt);
 *  BasicPassword::verifyPassword will return a boolean value that will be true if the password is valid
 *  and false if it is not.
 * 
 * @author Shawn Ewald <shawn.ewald@gmail.com>
 */
class BasicPassword {

    /**
     * @param string $password
     * @return string
     */
    public static function generatePassword (string $password) {
        return  password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @param string $passwordstring
     * @param string $hash
     * @return boolean
     */
    public static function verifyPassword (string $passwordstring, string $hash) {
        return password_verify($passwordstring, $hash);
    }

    /**
     * @param string $hash
     * @param string $passtring
     * @param string $salt
     * @param string $algo
     * @param int $iterations
     * @return string
     */
    public static function generatePBKDF2Password (string $passtring, string $salt, string $algo='sha256', int $iterations=1000) {
        return hash_pbkdf2($algo, $passtring, $salt, $iterations, 0, false);
    }

    /**
     * @param string $hash
     * @param string $passtring
     * @param string $salt
     * @param string $algo
     * @param int $iterations
     * @return boolean
     */
    public static function verifyPBKDF2Password (string $hash, string $passtring, string $salt, string $algo='sha256', int $iterations=1000) {
        return hash_pbkdf2($algo, $passtring, $salt, $iterations, 0, false) === $hash;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateSalt (int $length=255) {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}