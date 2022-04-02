

  export type LoginResponse = {
    result: true,
    nextToken: [number, string] | undefined,
}

export type LoginFailed = {
    result: false,
    reqOTP: boolean,
    reason: string,
}

export type LoginResponseWithKakao = LoginResponse | LoginFailed;

export type OTPResponse = {
    result: true,
    validUntil: string,
} | {
    result: false,
    reset: boolean,
    reason: string,
}


export type AutoLoginNonceResponse = {
    result: true,
    loginNonce: string,
};

export type AutoLoginResponse = {
    result: true,
    nextToken: [number, string] | undefined,
}

export type AutoLoginFailed = {
    result: false,
    silent: boolean,
    reason: string,
}