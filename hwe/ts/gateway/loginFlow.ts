import type { AutoLoginFailed } from '@/defs/API/Login';

export const OTP_REQUIRED_MESSAGE = '인증 코드 입력이 필요합니다.';

export type AutoLoginFailureAction = 'retry' | 'prompt_otp' | 'alert' | 'silent';

export function classifyAutoLoginFailure(result: AutoLoginFailed, attempt: number): AutoLoginFailureAction {
    if (result.reason === '자동 로그인: 절차 오류' && attempt === 0) {
        return 'retry';
    }
    if (result.reqOTP) {
        return 'prompt_otp';
    }
    if (result.silent) {
        return 'silent';
    }
    return 'alert';
}
