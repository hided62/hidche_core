import { assert } from 'chai';
import { classifyAutoLoginFailure, OTP_REQUIRED_MESSAGE } from '../ts/gateway/loginFlow';

describe('automatic login failure flow', () => {
    it('prompts for OTP without treating the challenge as a terminal login failure', () => {
        assert.equal(
            classifyAutoLoginFailure({
                result: false,
                silent: false,
                reqOTP: true,
                reason: '인증 코드를 입력해주세요',
            }, 0),
            'prompt_otp'
        );
        assert.equal(OTP_REQUIRED_MESSAGE, '인증 코드 입력이 필요합니다.');
    });

    it('keeps the existing retry and alert behavior for non-OTP failures', () => {
        assert.equal(
            classifyAutoLoginFailure({
                result: false,
                silent: false,
                reason: '자동 로그인: 절차 오류',
            }, 0),
            'retry'
        );
        assert.equal(
            classifyAutoLoginFailure({
                result: false,
                silent: false,
                reason: '로그인할 수 없습니다.',
            }, 1),
            'alert'
        );
        assert.equal(
            classifyAutoLoginFailure({
                result: false,
                silent: true,
                reason: 'failed',
            }, 1),
            'silent'
        );
    });
});
