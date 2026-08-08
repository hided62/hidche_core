<?php

namespace sammo\API\Login;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/sammo/API/Login/LoginByID.php';
require_once __DIR__ . '/../src/sammo/API/Login/LoginByToken.php';

final class LoginByTokenKakaoFailureTest extends TestCase
{
    public function testOtpChallengePreservesTokenAndReturnsPromptMetadata(): void
    {
        self::assertFalse(LoginByToken::shouldDiscardTokenAfterKakaoFailure(true));
        self::assertSame(321, LoginByToken::pendingSessionTokenID(true, 321));
        self::assertSame(
            [
                'result' => false,
                'silent' => false,
                'reqOTP' => true,
                'reason' => '인증 코드를 입력해주세요',
            ],
            LoginByToken::kakaoFailureResponse(true, '인증 코드를 입력해주세요')
        );
    }

    public function testNonOtpKakaoFailureKeepsLegacyErrorAndDiscardsToken(): void
    {
        self::assertTrue(LoginByToken::shouldDiscardTokenAfterKakaoFailure(false));
        self::assertNull(LoginByToken::pendingSessionTokenID(false, 321));
        self::assertSame(
            '카카오 API 앱이 등록되지 않았습니다.',
            LoginByToken::kakaoFailureResponse(false, '카카오 API 앱이 등록되지 않았습니다.')
        );
    }
}
