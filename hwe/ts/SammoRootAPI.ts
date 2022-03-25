import type { InvalidResponse } from "./defs";
import { APIPathGen } from "./util/APIPathGen";
import { callSammoAPI, done, type ValidResponse } from "./util/callSammoAPI";
export type { ValidResponse, InvalidResponse };

const apiRealPath = {
    Login: {
        LoginByID: done,
        LoginByToken: done,
        ReqNonce: done,
    },
} as const;

export const SammoRootAPI = APIPathGen(apiRealPath, (path: string[]) => {
    return (args?: Record<string, unknown>, returnError?: boolean) => {
        if (returnError) {
            return callSammoAPI(path.join('/'), args, true);
        }
        return callSammoAPI(path.join('/'), args);
    };
}) as typeof apiRealPath;