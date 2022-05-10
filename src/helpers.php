<?php
namespace ddn\api;
use ddn\api\Helpers\Functions_Placeholder;

class Helpers extends Functions_Placeholder {
    /**
     * Function that obtains a random string of a given length, using a standard alphanumeric characters set (a-z, A-Z, 0-9), or an extended set (a-z, A-Z, 0-9, !\"#$%&'()*+,-.:;<=>?[]^_{|}~, etc.)
     * @param $length the length of the string to generate (default: 8)
     * @param $extended if true, the extended set of characters is used (default: false)
     * @return the random string
     */
    static function get_random_string($length = 8, $extended = false) {
        // 218340105584896 combinations (> 2*10^14) vs UUID (> 5*10^36)
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        if ($extended) {
            $codeAlphabet.= "!\"#$%&'()*+,-.:;<=>?[]^_{|}~";
        }
        $max = strlen($codeAlphabet);
    
       for ($i=0; $i < $length; $i++) {
           $token .= $codeAlphabet[random_int(0, $max-1)];
       }
    
       return $token;
    }     
}
