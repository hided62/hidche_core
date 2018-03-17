<?php
require(__dir__.'/../vendor/autoload.php');

/**
 * 비밀번호 해시용 전역 SALT 반환
 * 비밀번호는 sha512(usersalt|sha512(globalsalt|password|globalsalt)|usersalt); 순임
 * 
 * @return string
 */
function getGlobalSalt(){
    return '_tK_globalSalt_';
}

/**
 * 서버 주소 반환. 서버의 경로가 하부 디렉토리인 경우에 하부 디렉토리까지 포함
 * 
 * @return string
 */
function getServerBasepath(){
    return '_tK_serverBasePath_';
}

/**
 * DB 객체 생성
 * 
 * @return MeekroDB 
 */
function getRootDB(){
    $host = '_tK_host_';
    $user = '_tK_user_';
    $password = '_tK_password_';
    $dbName = '_tK_dbName_';
    $port = _tK_port_;
    $encoding = 'utf8';

    static $uDB = NULL;

    if($uDB === NULL){
        $uDB = new MeekroDB($host,$user,$password,$dbName,$port,$encoding);
        $uDB->connect_options[MYSQLI_OPT_INT_AND_FLOAT_NATIVE] = true;
    }

    return $uDB;
}

function newMailObj(){
    $mailType = '_tK_mailType_';
    $mailSubType = '_tK_mailSubType_';
    $checkAuth = _mailCheckAuth_;//boolean
    $host = '_tK_mailHost_';
    $user = '_tK_mailUser_';
    $password = '_tK_mailPassword_';
    $address = '_tK_mailAddress_';
    $nickname = '_tK_mailNickname_';
    $port = _tK_mailPort_;//number
    $ignoreCert = _tK_mailIgnoreCert_;//boolean

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