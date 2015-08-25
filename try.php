<?php 

define('_PS_MYSQL_REAL_ESCAPE_STRING_', function_exists('mysql_real_escape_string'));
define('_PS_MAGIC_QUOTES_GPC_', get_magic_quotes_gpc());
define('_COOKIE_KEY_', 'dTfOtZ1U9aT80323OTPbDbu4WdmqqCmFXrU17RVKh1MeQ7o9bIauv2Bm');
define('MIN_PASSWD_LENGTH', 8);
$passwd='da9790844d1a0a82ee42dfe3d483c9dd';

function nl2br2($string){
	return str_replace(array("\r\n", "\r", "\n"), '', $string);
}

function pSQL($string, $htmlOK = false){
    if (_PS_MAGIC_QUOTES_GPC_)
            $string = stripslashes($string);
                if (!is_numeric($string))    {
                        $string = _PS_MYSQL_REAL_ESCAPE_STRING_ ? mysql_real_escape_string($string) : addslashes($string); 
                               if (!$htmlOK)  
                                         $string = strip_tags(nl2br2($string));    }  
                                                   return $string;}
function encrypt($passwd){ 
   return md5(_COOKIE_KEY_.$passwd);

}

//$passwd = Tools::encrypt($password = Tools::passwdGen(intval(MIN_PASSWD_LENGTH)));
//$encPasswd=md5(pSQL(_COOKIE_KEY_.$passwd));
//var_dump($encPasswd);
echo encrypt("ad807bdf0426766c05c64041124d30ce");

$pass="NREQ757542";
$newpass=md5('dTfOtZ1U9aT80323OTPbDbu4WdmqqCmFXrU17RVKh1MeQ7o9bIauv2Bm'.$pass);
var_dump($newpass);
function decryptIt( $q ) {
    $cryptKey  = 'qJB0rGtIn5UB1xG03efyCp';
    $qDecoded      = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $q ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
    return( $qDecoded );
}
$input = "SmackFactory";

$encrypted = decryptIt( $input );

