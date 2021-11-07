<?php

namespace sammo\API\Login;

use DateTime;
use sammo\Session;
use DateTimeInterface;
use sammo\BaseAPI;
use sammo\Json;
use sammo\KakaoUtil;
use sammo\RootDB;
use sammo\TimeUtil;
use sammo\Util;
use sammo\Validator;

class LoginByID extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v
            ->rule('required', [
                'username',
                'password'
            ]);

        if (!$v->validate()) {
            return $v->errorStr();
        }
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return BaseAPI::NO_SESSION;
    }

    public function scrubToken(int $userID)
    {
        $RootDB = RootDB::db();
        $nowDate = TimeUtil::now();
        $RootDB->delete(
            'login_token',
            'user_id = %i AND expire_date < %s',
            $userID,
            $nowDate
        );
        $RootDB->query(
            'DELETE invalid FROM login_token AS invalid
           JOIN
               ( SELECT id
                 FROM login_token
                 WHERE user_id = %i
                 ORDER BY id DESC
                   LIMIT 8,1
               ) AS valid
             ON invalid.id < valid.id WHERE user_id = %i',
            $userID,
            $userID
        );
    }

    public function addToken(int $userID)
    {
        $RootDB = RootDB::db();
        $nowDate = TimeUtil::now();
        $token = Util::randomStr(20);
        $RootDB->insert('login_token', [
            'user_id' => $userID,
            'base_token' => $token,
            'reg_ip' => Util::get_client_ip(true),
            'reg_date' => $nowDate,
            'expire_date' => TimeUtil::nowAddDays(7)
        ]);
        $tokenID = $RootDB->insertId();
        return [$tokenID, $token];
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
    {
        $RootDB = RootDB::db();
        if ($session->isLoggedIn()) {
            $session->logout();
        }

        $username = $this->args['username'];
        $password = $this->args['password'];

        $userInfo = $RootDB->queryFirstRow(
            'SELECT `no`, `id`, `name`, `grade`, `delete_after`, `acl`, oauth_id, oauth_type, oauth_info, token_valid_until ' .
                'from member where id=%s_username AND ' .
                'pw=sha2(concat(salt, %s_password, salt), 512)',
            [
                'username' => $username,
                'password' => $password
            ]
        );

        if (!$userInfo) {
            return '아이디나 비밀번호가 올바르지 않습니다.';
        }

        $canLogin = $RootDB->queryFirstField('SELECT `LOGIN` FROM `system` WHERE `NO` = 1');
        if ($canLogin != 'Y' && $userInfo['grade'] < 5) {
            return '현재는 로그인이 금지되어있습니다!';
        }


        $nowDate = TimeUtil::now();
        if ($userInfo['delete_after']) {
            if ($userInfo['delete_after'] < $nowDate) {
                $RootDB->delete('member', 'no=%i', $userInfo['no']);
                return [
                    'result' => false,
                    'reqOTP' => false,
                    'reason' => "기간 만기로 삭제되었습니다. 재 가입을 시도해주세요."
                ];
            } else {
                return [
                    'result' => false,
                    'reqOTP' => false,
                    'reason' => "삭제 요청된 계정입니다.[{$userInfo['delete_after']}]"
                ];
            }
        }

        $RootDB->insert('member_log', [
            'member_no' => $userInfo['no'],
            'action_type' => 'login',
            'action' => Json::encode([
                'ip' => Util::get_client_ip(true),
                'type' => 'plain'
            ])
        ]);

        if ($userInfo['oauth_type'] == 'KAKAO') {
            $oauthFailResult = KakaoUtil::kakaoOAuthCheck($userInfo);
            if ($oauthFailResult !== null) {
                $session->login($userInfo['no'], $userInfo['id'], $userInfo['grade'], true, $userInfo['token_valid_until'], null, Json::decode($userInfo['acl'] ?? '{}'));
                [$oauthReqOTP, $oauthFailReason] = $oauthFailResult;
                return [
                    'result' => false,
                    'reqOTP' => $oauthReqOTP,
                    'reason' => $oauthFailReason
                ];
            }
        }

        $this->scrubToken($userInfo['no']);
        $nextToken = $this->addToken($userInfo['no']);

        $session->login($userInfo['no'], $userInfo['id'], $userInfo['grade'], false, $userInfo['token_valid_until'], $nextToken[0], Json::decode($userInfo['acl'] ?? '{}'));


        return [
            'result' => true,
            'reqOTP' => false,
            'nextToken' => $nextToken,
            'reason' => '로그인 되었습니다.'
        ];
    }
}
