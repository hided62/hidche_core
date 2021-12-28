import { InvalidResponse } from "./defs";
import { APIPathGen } from "./util/APIPathGen";
import { callSammoAPI, ValidResponse } from "./util/callSammoAPI";
export type { ValidResponse, InvalidResponse };

async function done<ResultType extends ValidResponse>(args?: Record<string, unknown>): Promise<ResultType>;
async function done<ResultType extends ValidResponse>(args: Record<string, unknown> | undefined, returnError: false): Promise<ResultType>;
async function done<ResultType extends ValidResponse, ErrorType extends InvalidResponse>(args: Record<string, unknown> | undefined, returnError: true): Promise<ResultType | ErrorType>;

async function done<ResultType extends ValidResponse, ErrorType extends InvalidResponse>(args?: Record<string, unknown>, returnError = false): Promise<ResultType | ErrorType>{
    console.error(`Can't directly call. ${args}, ${returnError}. Use auto-generated path API.`);
    return callSammoAPI<ResultType, ErrorType>([], args, true);
}

const apiRealPath = {
    Login: {
        LoginByID: done,
        LoginByToken: done,
        ReqNonce: done,
    },
} as const;

export const SammoRootAPI = APIPathGen(apiRealPath);