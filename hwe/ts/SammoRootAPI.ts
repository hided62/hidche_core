import type { AutoLoginFailed, AutoLoginNonceResponse, AutoLoginResponse } from "./defs/API/Login";
import { APIPathGen } from "./util/APIPathGen";
import { callSammoAPI, extractHttpMethod, GET, POST, type APICallT, type APITail, type InvalidResponse, type RawArgType, type ValidResponse } from "./util/callSammoAPI";
export type { ValidResponse, InvalidResponse };

const apiRealPath = {
    Login: {
        LoginByID: POST,
        LoginByToken: POST as APICallT<{
            hashedToken: string,
            token_id: number,
        }, AutoLoginResponse, AutoLoginFailed>,
        ReqNonce: GET as APICallT<undefined, AutoLoginNonceResponse, AutoLoginFailed>
    },
} as const;

export const SammoRootAPI = APIPathGen(apiRealPath, (path: string[], tail: APITail, pathParam) => {
    const method = extractHttpMethod(tail);
    return (args?: RawArgType, returnError?: boolean) => {
        if (returnError) {
            return callSammoAPI(method, path.join('/'), args, pathParam, true);
        }
        return callSammoAPI(method, path.join('/'), args, pathParam);
    };
});