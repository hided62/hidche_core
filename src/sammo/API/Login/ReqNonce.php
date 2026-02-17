<?php

namespace sammo\API\Login;

use sammo\Session;
use DateTimeInterface;
use sammo\BaseAPI;
use sammo\Enums\APIRecoveryType;
use sammo\Json;
use sammo\KakaoUtil;
use sammo\RootDB;
use sammo\TimeUtil;
use sammo\Util;
use sammo\Validator;

class ReqNonce extends BaseAPI
{

    public function getRequiredSessionMode(): int
    {
        return \sammo\BaseAPI::NO_LOGIN;
    }

    public function validateArgs(): ?string
    {
        return null;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $loginNonce = Util::randomStr(16);
        $loginNonceExpired = TimeUtil::nowAddSeconds(2);
        $session->loginNonce = $loginNonce;
        $session->loginNonceExpired = $loginNonceExpired;
        $session->loginNonceDebug = Json::encode([
            'phase' => 'issue_nonce',
            'sid' => session_id(),
            'cookie_sid' => $_COOKIE[session_name()] ?? null,
            'issued_at' => TimeUtil::now(true),
            'host' => gethostname() ?: php_uname('n'),
            'php' => PHP_VERSION,
            'session_save_handler' => ini_get('session.save_handler') ?: null,
            'session_save_path' => ini_get('session.save_path') ?: null,
            'session_use_strict_mode' => ini_get('session.use_strict_mode') ?: null,
            'session_cookie_samesite' => ini_get('session.cookie_samesite') ?: null,
            'session_cookie_secure' => ini_get('session.cookie_secure') ?: null,
        ]);
        return [
            'result' => true,
            'loginNonce' => $loginNonce,
        ];
    }
}
