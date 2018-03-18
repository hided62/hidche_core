<?php
require_once('_common.php');
require_once(__dir__.'/../d_setting/conf.php');
require_once(__dir__.'/../f_func/func.php');
require_once(ROOT.'/f_func/class._Time.php');

function checkUsernameDup($username){
    if(!$username){
        return '계정명을 입력해주세요';
    }

    $length = strlen($username);
    if($length < 4 || $length > 64){
        return '적절하지 않은 길이입니다.';
    }

    $cnt = getRootDB()->queryFirstField('SELECT count(no) FROM member WHERE `id` = %s LIMIT 1', $username);
    if($cnt != 0){
        return '이미 사용중인 계정명입니다';
    }
    return true;
}

function checkNicknameDup($nickname){
    if(!$nickname){
        return '닉네임을 입력해주세요';
    }

    $length = strlen($nickname);
    if($length < 1 || $length > 6){
        return '적절하지 않은 길이입니다.';
    }

    $cnt = getRootDB()->queryFirstField('SELECT count(no) FROM member WHERE `name` = %s LIMIT 1', $nickname);
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

    $userInfo = getRootDB()->queryFirstField('SELECT `no`, `delete_after` FROM member WHERE `email` = %s LIMIT 1', $email);
    if($userInfo){
        $nowDate = _Time::DatetimeNow();
        if (!$userInfo['delete_after']) {
            return '이미 사용중인 이메일입니다. 관리자에게 문의해주세요.';
        }

        if($userInfo['delete_after'] >= $userInfo){
            return "삭제 요청된 계정입니다.[{$userInfo['delete_after']}]";
        }

        //$userInfo['delete_after'] < $userInfo
        getRootDB()->delete('member', 'no=%i', $userInfo['no']);
    }
    return true;
}