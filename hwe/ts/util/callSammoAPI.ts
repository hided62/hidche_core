import axios from "axios";
import { isArray } from "lodash";
import { InvalidResponse } from '@/defs';

export type ValidResponse = {
    result: true
}

export async function callSammoAPI<ResultType extends ValidResponse>(path: string | string[], args?: Record<string, unknown>): Promise<ResultType>;
export async function callSammoAPI<ResultType extends ValidResponse>(path: string | string[], args: Record<string, unknown> | undefined, returnError: false): Promise<ResultType>;
export async function callSammoAPI<ResultType extends ValidResponse, ErrorType extends InvalidResponse>(path: string | string[], args: Record<string, unknown> | undefined, returnError: true): Promise<ResultType | ErrorType>;

export async function callSammoAPI<ResultType extends ValidResponse, ErrorType extends InvalidResponse>(path: string | string[], args?: Record<string, unknown>, returnError = false): Promise<ResultType | ErrorType> {
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