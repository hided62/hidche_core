import '@scss/gateway/login.scss';

import $ from 'jquery';
import { JQValidateForm, NamedRules } from '@util/jqValidateForm';
import axios from 'axios';
import { convertFormData } from '@util/convertFormData';
import { setAxiosXMLHttpRequest } from '@util/setAxiosXMLHttpRequest';
import { unwrap_any } from '@util/unwrap_any';
import { sha512 } from 'js-sha512';
import { unwrap } from '@util/unwrap';
import { InvalidResponse } from '@/defs';
import { delay } from '@util/delay';
import { Modal } from 'bootstrap';
import '@/gateway/common';

type LoginResponse = {
    result: true,
    nextToken: [number, string] | undefined,
} | {
    result: false,
    reqOTP: boolean,
    reason: string,
}

type OTPResponse = {
    result: true,
    validUntil: string,
} | {
    result: false,
    reset: boolean,
    reason: string,
}

type AutoLoginNonceResponse = {
    result: true,
    loginNonce: string,
} | InvalidResponse;

type AutoLoginResponse = {
    result: true,
    nextToken: [number, string] | undefined,
} | {
    result: false,
    silent: boolean,
    reason: string,
}
declare global {
    interface Window {
        getOAuthToken: (mode: string, scope_list: string[]) => void;
        postOAuthResult: (mode: string) => void;
    }
}

declare const kakao_oauth_client_id: string;
declare const kakao_oauth_redirect_uri: string;

let modalOTP: Modal | undefined = undefined;

let oauthMode: string | null = null;

const TOKEN_VERSION = 1;
const LOGIN_TOKEN_KEY = 'sammo_login_token';
function regNextToken(tokenInfo: [number, string]) {
    localStorage.setItem(LOGIN_TOKEN_KEY, JSON.stringify([TOKEN_VERSION, tokenInfo, Date.now()]));
}

function getToken(): [number, string] | undefined {
    const trialToken = localStorage.getItem(LOGIN_TOKEN_KEY);
    if (!trialToken) {
        return;
    }
    const tokenItems = JSON.parse(trialToken);
    if (tokenItems[0] != TOKEN_VERSION) {
        console.log(tokenItems);
        resetToken();
        return;
    }
    const [, token,] = tokenItems;
    return token;
}

function resetToken() {
    localStorage.removeItem(LOGIN_TOKEN_KEY);
}

async function tryAutoLogin() {
    try {
        const tokenInfo = getToken();
        if (!tokenInfo) {
            return;
        }

        const [tokenID, token] = tokenInfo;

        const result = await axios.post<AutoLoginNonceResponse>('api.php', {
            path: ["Login", "ReqNonce"]
        });

        if (!result) {
            //api 에러.
            return;
        }

        if (!result.data.result) {
            resetToken();
            return;
        }

        const nonce = result.data.loginNonce;

        const hashedToken = sha512(token + nonce);
        const _loginResult = await axios.post<AutoLoginResponse>('api.php', {
            path: ["Login", "LoginByToken"],
            args: {
                'hashedToken': hashedToken,
                'token_id': tokenID,
            }
        });

        if (!_loginResult) {
            return;
        }

        const loginResult = _loginResult.data;
        if (!loginResult.result) {
            if (!loginResult.silent) {
                alert(loginResult.reason);
            }
            console.error(loginResult.reason);
            return;
        }

        if (loginResult.nextToken) {
            regNextToken(loginResult.nextToken);
        }
        window.location.href = "./";

    }
    catch (e) {
        console.error(e);
        return;
    }


}

function getOAuthToken(mode: string, scope_list?: string[] | string) {
    if (mode === undefined) {
        mode = 'login';
    }
    oauthMode = mode;
    let url = `https://kauth.kakao.com/oauth/authorize?client_id=${kakao_oauth_client_id}&redirect_uri=${kakao_oauth_redirect_uri}&response_type=code`;
    if (Array.isArray(scope_list)) {
        url += `&scope=${scope_list.join(',')}`;
    } else if (typeof scope_list === 'string') {
        url += `&scope=${scope_list}`;
    }

    window.open(url, "KakaoAccountLogin", "width=600,height=450,resizable=yes,scrollbars=yes");
}
window.getOAuthToken = getOAuthToken;

async function sendTempPasswordToKakaoTalk(): Promise<void> {

    let result: InvalidResponse;

    try {
        const response = await axios({
            url: 'oauth_kakao/j_login_oauth.php',
            responseType: 'json',
            method: 'post',
        });
        result = response.data;
    }
    catch (e) {
        console.error(e);
        alert(`실패했습니다_login: ${e}`);
        return;
    }

    if (!result.result) {
        alert(result.reason);
        return;
    }

    try {
        const response = await axios({
            url: 'oauth_kakao/j_change_pw.php',
            responseType: 'json',
            method: 'post',
        });
        result = response.data;
    }
    catch (e) {
        console.error(e);
        alert(`실패했습니다_pw: ${e}`);
        return;
    }

    alert('임시 비밀번호가 카카오톡으로 전송되었습니다.');
}

async function doLoginUsingOAuth() {
    let result: LoginResponse;

    try {
        const response = await axios({
            url: 'oauth_kakao/j_login_oauth.php',
            responseType: 'json',
            method: 'post',
        });
        result = response.data;
    }
    catch (e) {
        console.error(e);
        alert(`실패했습니다_login: ${e}`);
        return;
    }

    if (result.result) {
        if (result.nextToken) {
            regNextToken(result.nextToken);
        }
        window.location.href = "./";
        return;
    }

    if (!result.reqOTP) {
        alert(result.reason);
        return;
    }

    const modalEl = unwrap(document.querySelector('#modalOTP'))
    if (!modalOTP) {
        modalOTP = new Modal(modalEl);
        modalEl.addEventListener('shown.bs.modal', function () {
            unwrap(document.querySelector<HTMLElement>('#otp_code')).focus();
        });
    }
    modalOTP.show();
}

function postOAuthResult(mode: string) {
    if (mode == 'join') {
        window.location.href = 'oauth_kakao/join.php';
        return;
    }

    if (mode == 'req_email') {
        alert('이메일 정보 공유를 허가해 주셔야 합니다.');
        return;
    }

    if (mode == 'login') {
        console.log('로그인모드');
        if (oauthMode == 'change_pw') {
            void sendTempPasswordToKakaoTalk();
        } else {
            void doLoginUsingOAuth();
        }
        return;
    }

    alert('예외 발생!');
}
window.postOAuthResult = postOAuthResult;


$(async function ($) {
    setAxiosXMLHttpRequest();

    //로그인 먼저 해볼 것
    if (getToken()) {
        void tryAutoLogin();
        await delay(100);
    }


    type LoginFormType = {
        username: string,
        password: string,
    };
    const descriptor: NamedRules<LoginFormType> = {
        username: {
            required: true,
            type: 'string',
            message: '유저명을 입력해주세요',
        },
        password: {
            required: true,
            type: 'string',
            message: '비밀번호를 입력해주세요'
        }
    };

    const validator = new JQValidateForm($('#main_form'), descriptor);
    validator.installChangeHandler();

    $("#main_form").on('submit', async function (e) {
        e.preventDefault();

        const values = await validator.validate();
        if (values === undefined) {
            return;
        }
        const raw_password = values.password;
        const salt = unwrap_any<string>($('#global_salt').val());
        const hash_pw = sha512(salt + raw_password + salt);

        let result: LoginResponse;

        try {
            const response = await axios({
                url: 'api.php',
                responseType: 'json',
                method: 'post',
                data: {
                    path: ["Login", "LoginByID"],
                    args: {
                        username: values.username,
                        password: hash_pw,
                    }
                }
            });
            result = response.data;
        }
        catch (e) {
            console.error(e);
            alert(`실패했습니다: ${e}`);
            return;
        }


        if (result.result) {
            if (result.nextToken) {
                regNextToken(result.nextToken);
            }
            window.location.href = "./";
            return;
        }

        if (!result.reqOTP) {
            alert(result.reason);
            return;
        }

        const modalEl = unwrap(document.querySelector('#modalOTP'))
        if (!modalOTP) {
            modalOTP = new Modal(modalEl);
            modalEl.addEventListener('shown.bs.modal', function () {
                unwrap(document.querySelector<HTMLElement>('#otp_code')).focus();
            });
        }
        modalOTP.show();
    });

    $('#otp_form').on('submit', async function (e) {
        e.preventDefault();

        let result: OTPResponse;

        try {
            const response = await axios({
                url: 'oauth_kakao/j_check_OTP.php',
                responseType: 'json',
                method: 'post',
                data: convertFormData({
                    otp: unwrap_any<string>($('#otp_code').val()),
                })
            });
            result = response.data;
        }
        catch (e) {
            console.error(e);
            alert(`실패했습니다: ${e}`);
            return;
        }

        if (result.result) {
            alert(`로그인되었습니다. ${result.validUntil}까지 유효합니다.`);
            window.location.href = "./";
            return;
        }

        alert(result.reason);

        if (result.reset) {
            if (modalOTP) {
                modalOTP.hide();
            }
            return;
        }
    });

    $('#oauth_change_pw').on('click', function (e) {
        e.preventDefault();
        getOAuthToken('change_pw', 'talk_message');
    });

    $('#btn_kakao_login').on('click', function (e) {
        e.preventDefault();
        getOAuthToken('login', ['account_email', 'talk_message']);
    })

    //TODO: 모바일에서 크기 비례 지도 다시 띄우기?
    /*
    if (document.body.clientWidth < 700) {
        const targetWidth = document.body.clientWidth * 0.9;
        const scale = targetWidth / 700;
        const $map = $('#running_map');
        $map.find('.col').css('max-width', targetWidth);
        $map.find('.card').css('width', targetWidth);
        $map.find('.map-container').css({
            'transform-origin': 'top left',
            'transform': `scale(${scale}, ${scale})`,
            'height': 500 * scale,
        });
        $map.find('.map_body').data('scale', scale);
    }*/
});

window.fitIframe = function () {
    const iframe = unwrap(document.querySelector<HTMLIFrameElement>('#running_map'));//TODO: 근황 여러개 볼 수 있도록?
    const scrollHeight = unwrap(iframe.contentWindow).document.body.scrollHeight;
    iframe.style.height = `${scrollHeight}px`;
}