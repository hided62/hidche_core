import axios from "axios";
import { isArray } from "lodash";
import type { InvalidResponse } from '@/defs';

export type ValidResponse = {
    result: true
}

export type RawArgType = Record<string,  unknown>|Record<string, unknown>[];

export interface CallbackT<ArgType extends RawArgType, ResultType extends ValidResponse = ValidResponse, ErrorType extends InvalidResponse = InvalidResponse>{
    (args?: ArgType): Promise<ResultType>;
    (args: ArgType | undefined, returnError: false): Promise<ResultType>;
    (args: ArgType | undefined, returnError: true): Promise<ResultType | ErrorType>;
}

export async function callSammoAPI<ResultType extends ValidResponse>(path: string | string[], args?: Record<string, unknown> | Record<string, unknown>[]): Promise<ResultType>;
export async function callSammoAPI<ResultType extends ValidResponse>(path: string | string[], args: Record<string, unknown> | Record<string, unknown>[] | undefined, returnError: false): Promise<ResultType>;
export async function callSammoAPI<ResultType extends ValidResponse, ErrorType extends InvalidResponse>(path: string | string[], args: Record<string, unknown> | Record<string, unknown>[] | undefined, returnError: true): Promise<ResultType | ErrorType>;

export async function callSammoAPI<ResultType extends ValidResponse, ErrorType extends InvalidResponse>(path: string | string[], args?: Record<string, unknown> | Record<string, unknown>[], returnError = false): Promise<ResultType | ErrorType> {
    if (isArray(path)) {
        path = path.join('/');
    }

    const response = await axios({
        url: `api.php?path=${path}`,
        method: "post",
        responseType: "json",
        data: args
    });
    const result: ErrorType | ResultType = response.data;
    if (!result.result) {
        if (returnError) {
            return result;
        }
        throw result.reason;
    }
    return result;
}

export async function done<ResultType extends ValidResponse>(args?: RawArgType): Promise<ResultType>;
export async function done<ResultType extends ValidResponse>(args: RawArgType | undefined, returnError: false): Promise<ResultType>;
export async function done<ResultType extends ValidResponse, ErrorType extends InvalidResponse>(args: RawArgType | undefined, returnError: true): Promise<ResultType | ErrorType>;

export async function done<ResultType extends ValidResponse, ErrorType extends InvalidResponse>(args?: RawArgType, returnError = false): Promise<ResultType | ErrorType> {
    console.error(`Can't directly call. ${args}, ${returnError}. Use auto-generated path API.`);
    return callSammoAPI<ResultType, ErrorType>([], args, true);
}