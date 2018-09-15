<?php
namespace sammo;
use \kakao\Kakao_REST_API_Helper as Kakao_REST_API_Helper;

function checkUsernameDup($username){
    if(!$username){
        return '계정명을 입력해주세요';
    }

    $username = mb_strtolower($username, 'utf-8');
    $length = strlen($username);
    if($length < 4 || $length > 64){
        return '적절하지 않은 길이입니다.';
    }

    $cnt = RootDB::db()->queryFirstField('SELECT count(no) FROM member WHERE `id` = %s LIMIT 1', $username);
    if($cnt != 0){
        return '이미 사용중인 계정명입니다';
    }
    return true;
}

function checkNicknameDup($nickname){
    if(!$nickname){
        return '닉네임을 입력해주세요';
    }

    $length = mb_strlen($nickname, 'utf-8');
    if($length < 1 || $length > 9){
        return '적절하지 않은 길이입니다.';
    }

    $cnt = RootDB::db()->queryFirstField('SELECT count(no) FROM member WHERE `name` = %s LIMIT 1', $nickname);
    if($cnt != 0){
        return '이미 사용중인 닉네임입니다';
    }
    return true;
}


function checkEmailDup($email){
    if(!$email){
        return '이메일을 입력해주세요';
    }

    $length = strlen($email);
    if($length < 1 || $length > 64){
        return '적절하지 않은 길이입니다.';
    }

    $userInfo = RootDB::db()->queryFirstField('SELECT `no`, `delete_after` FROM member WHERE `email` = %s LIMIT 1', $email);
    if($userInfo){
        $nowDate = TimeUtil::now();
        if (!$userInfo['delete_after']) {
            return '이미 사용중인 이메일입니다. 관리자에게 문의해주세요.';
        }

        if($userInfo['delete_after'] >= $userInfo){
            return "삭제 요청된 계정입니다.[{$userInfo['delete_after']}]";
        }

        //$userInfo['delete_after'] < $userInfo
        RootDB::db()->delete('member', 'no=%i', $userInfo['no']);
    }
    return true;
}

function createOTPbyUserNO(int $userNo):bool{
    $userInfo = RootDB::db()->queryFirstRow('SELECT oauth_info FROM member WHERE no=%i', $userNo);
    if(!$userInfo){
        return false;
    }

    $oauthInfo = Json::decode($userInfo['oauth_info']);
    if(!$oauthInfo){
        return false;
    }

    $accessToken = $oauthInfo['accessToken'];
    $OTPValue = $oauthInfo['OTPValue']??null;
    $OTPTrialUntil = $oauthInfo['OTPTrialUntil']??null;

    $now = TimeUtil::now();


    if($OTPTrialUntil && $OTPValue && $OTPTrialUntil > $now){
        return true;
    }

    [$OTPValue, $OTPTrialUntil] = createOTP($accessToken);

    if(!$OTPValue){
        return false;
    }

    $oauthInfo['OTPValue'] = $OTPValue;
    $oauthInfo['OTPTrialUntil'] = $OTPTrialUntil;
    $oauthInfo['OTPTrialCount'] = 3;

    RootDB::db()->update('member', [
        'oauth_info'=>Json::encode($oauthInfo)
    ], 'no=%i', $userNo);

    return true;
}

function createOTP(string $accessToken):?array{
    $restAPI = new Kakao_REST_API_Helper($accessToken);

    $OTPValue = Util::randRangeInt(10000, 99999);
    $OTPTrialUntil = TimeUtil::nowAddSeconds(180);

    $sendResult = $restAPI->talk_to_me_default([
        "object_type"=> "text",
        "text"=> "인증 코드는 $OTPValue 입니다. $OTPTrialUntil 이내에 입력해주세요.",
        "link"=> [
          "web_url"=> ServConfig::getServerBasepath(),
          "mobile_web_url" => ServConfig::getServerBasepath()
        ],
        "button_title"=> "로그인 페이지 열기"
      ]);
      $sendResult['code'] = Util::array_get($sendResult['code'], 0);
      if($sendResult['code'] < 0){
          return null;
      }

      return [$OTPValue, $OTPTrialUntil];
}

