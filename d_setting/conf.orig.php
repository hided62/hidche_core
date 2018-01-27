<?php
require_once(__dir__.'/../e_lib/util.php');
require_once(__dir__.'/../e_lib/meekrodb.2.3.class.php.php');
require_once(__dir__.'/../e_lib/phpmailer5/class.phpmailer.php');
require_once(__dir__.'/../e_lib/phpmailer5/class.smtp.php');

/**
 * DB 객체 생성
 * 
 * @return MeekroDB 
 */
function newRootDB(){
    $host = '_host_';
    $user = '_user_';
    $password = '_password_';
    $dbName = '_dbName_';
    $port = _port_;
    $encoding = 'utf8';

    static $uDB = NULL;

    if($uDB === NULL){
        $uDB = new MeekroDB($host,$user,$password,$dbName,$port,$encoding);
    }

    return $uDB;
}

function newMailObj(){
    $mailType = '_mailType_';
    $mailSubType = '_mailSubType_';
    $checkAuth = _mailCheckAuth_;//boolean
    $host = '_mailHost_';
    $user = '_mailUser_';
    $password = '_mailPassword_';
    $address = '_mailAddress_';
    $nickname = '_mailNickname_';
    $port = _mailPort_;//number
    $ignoreCert = _mailIgnoreCert_;//boolean

    if($mailType == 'smtp'){
        $objMail = new PHPMailer();
        $objMail->isSMTP();
        $objMail->setFrom($address);
        $objMail->Hostname = $host;
        $objMail->FromName = $nickname;
        $objMail->Port = $port;

        if($checkAuth){
            $objMail->SMTPAuth = true;
            $objMail->Username = $user;
            $objMail->Password = $password;
            
        }
        if($ignoreCert){
            $objMail->SMTPOptions = array (
                'ssl' => array(
                    'verify_peer'  => false,
                    'allow_self_signed' => true
                )
            );
        }

        if($mailSubType == 'tls'){
            $objMail->SMTPSecure = 'tls';
        }
        else if($mailSubType == 'ssl'){
            $objMail->SMTPSecure = 'ssl';
        }

        return $objMail;
    }
    else if($mailType == 'mail'){
        throw new BadMethodCallException('Not Implemented');
    }
    else if($mailType == 'google'){
        throw new BadMethodCallException('Not Implemented');
    }
    else{
        throw new InvalidArgumentException('Invalid Mail Type');
    }
    
    
    


}