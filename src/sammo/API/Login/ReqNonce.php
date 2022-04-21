<?php

namespace sammo\API\Login;

use sammo\Session;
use DateTimeInterface;
use sammo\BaseAPI;
use sammo\Json;
use sammo\KakaoUtil;
use sammo\RootDB;
use sammo\TimeUtil;
use sammo\Util;
use sammo\Validator;

class ReqNonce extends BaseAPI{

    public function getRequiredSessionMode(): int {
        return \sammo\BaseAPI::NO_LOGIN;
    }

    public function validateArgs(): ?string
    {
        return null;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag) {
        $loginNonce = Util::randomStr(16);
        $loginNonceExpired = TimeUtil::nowAddSeconds(2);
        $session->loginNonce = $loginNonce;
        $session->loginNonceExpired = $loginNonceExpired;
        return [
            'result'=>true,
            'loginNonce'=>$loginNonce,
        ];
    }

}