import ky from 'ky';
import { isArray, isEmpty } from "lodash";

export type ValidResponse = {
    result: true
}

export type InvalidResponse = {
    result: false;
    reason: string;
}


export type RawArgType = Record<string, unknown> | Record<string, unknown>[] | undefined;

export interface APICallT<ArgType extends RawArgType, ResultType extends ValidResponse = ValidResponse, ErrorType extends InvalidResponse = InvalidResponse> {
    (args?: ArgType): Promise<ResultType>;
    (args: ArgType | undefined, returnError: false): Promise<ResultType>;
    (args: ArgType | undefined, returnError: true): Promise<ResultType | ErrorType>;
}

type HttpMethod = 'get' | 'post' | 'put' | 'patch' | 'head' | 'delete';
export type APITail = typeof GET | typeof POST | typeof PUT | typeof PATCH | typeof HEAD | typeof DELETE;

const httpMethodMap = new Map<APITail, HttpMethod>([
    [GET, 'get'],
    [POST, 'post'],
    [PUT, 'put'],
    [PATCH, 'patch'],
    [HEAD, 'head'],
    [DELETE, 'delete'],
]);

export function extractHttpMethod(tail: APITail): HttpMethod {
    return httpMethodMap.get(tail) ?? 'post';
}

export async function callSammoAPI<ResultType extends ValidResponse>(method: HttpMethod, path: string | string[], args: RawArgType, paramArgs: Record<string, string | number> | undefined): Promise<ResultType>;
export async function callSammoAPI<ResultType extends ValidResponse>(method: HttpMethod, path: string | string[], args: RawArgType, paramArgs: Record<string, string | number> | undefined, returnError: false): Promise<ResultType>;
export async function callSammoAPI<ResultType extends ValidResponse, ErrorType extends InvalidResponse>(method: HttpMethod, path: string | string[], args: RawArgType, paramArgs: Record<string, string | number> | undefined, returnError: true): Promise<ResultType | ErrorType>;
export async function callSammoAPI<ResultType extends ValidResponse, ErrorType extends InvalidResponse>(method: HttpMethod, path: string | string[], args: RawArgType, paramArgs: Record<string, string | number> | undefined, returnError = false): Promise<ResultType | ErrorType> {
    if (isArray(path)) {
        path = path.join('/');
    }

    if (args && isEmpty(args)) {
        args = undefined;
    }

    const result = await ky('api.php', {
        searchParams: {
            ...paramArgs,
            path,
        },
        method,
        json: args,
        headers: {
            'content-type': 'application/json'
        }
    }).json() as ErrorType | ResultType;

    if (!result.result) {
        if (returnError) {
            return result;
        }
        throw result.reason;
    }
    return result;
}

export async function GET<ResultType extends ValidResponse, ArgType extends undefined = undefined>(args?: ArgType): Promise<ResultType>;
export async function GET<ResultType extends ValidResponse, ArgType extends undefined = undefined>(args: ArgType | undefined, returnError: false): Promise<ResultType>;
export async function GET<ResultType extends ValidResponse, ErrorType extends InvalidResponse, ArgType extends undefined = undefined>(args: ArgType | undefined, returnError: true): Promise<ResultType | ErrorType>;
export async function GET<ResultType extends ValidResponse, ErrorType extends InvalidResponse, ArgType extends undefined = undefined>(args?: ArgType, returnError = false): Promise<ResultType | ErrorType> {
    console.error(`Can't directly call GET. ${args}, ${returnError}. Use auto-generated path API.`);
    return callSammoAPI<ResultType, ErrorType>('get', [], args, undefined, true);
}

export async function POST<ResultType extends ValidResponse, ArgType extends RawArgType = RawArgType>(args?: ArgType): Promise<ResultType>;
export async function POST<ResultType extends ValidResponse, ArgType extends RawArgType = RawArgType>(args: ArgType | undefined, returnError: false): Promise<ResultType>;
export async function POST<ResultType extends ValidResponse, ErrorType extends InvalidResponse, ArgType extends RawArgType = RawArgType>(args: ArgType | undefined, returnError: true): Promise<ResultType | ErrorType>;
export async function POST<ResultType extends ValidResponse, ErrorType extends InvalidResponse, ArgType extends RawArgType = RawArgType>(args?: ArgType, returnError = false): Promise<ResultType | ErrorType> {
    console.error(`Can't directly call POST. ${args}, ${returnError}. Use auto-generated path API.`);
    return callSammoAPI<ResultType, ErrorType>('post', [], args, undefined, true);
}

export async function PUT<ResultType extends ValidResponse, ArgType extends RawArgType = RawArgType>(args?: ArgType): Promise<ResultType>;
export async function PUT<ResultType extends ValidResponse, ArgType extends RawArgType = RawArgType>(args: ArgType | undefined, returnError: false): Promise<ResultType>;
export async function PUT<ResultType extends ValidResponse, ErrorType extends InvalidResponse, ArgType extends RawArgType = RawArgType>(args: ArgType | undefined, returnError: true): Promise<ResultType | ErrorType>;
export async function PUT<ResultType extends ValidResponse, ErrorType extends InvalidResponse, ArgType extends RawArgType = RawArgType>(args?: ArgType, returnError = false): Promise<ResultType | ErrorType> {
    console.error(`Can't directly call PUT. ${args}, ${returnError}. Use auto-generated path API.`);
    return callSammoAPI<ResultType, ErrorType>('put', [], args, undefined, true);
}

export async function PATCH<ResultType extends ValidResponse, ArgType extends RawArgType = RawArgType>(args?: ArgType): Promise<ResultType>;
export async function PATCH<ResultType extends ValidResponse, ArgType extends RawArgType = RawArgType>(args: ArgType | undefined, returnError: false): Promise<ResultType>;
export async function PATCH<ResultType extends ValidResponse, ErrorType extends InvalidResponse, ArgType extends RawArgType = RawArgType>(args: ArgType | undefined, returnError: true): Promise<ResultType | ErrorType>;
export async function PATCH<ResultType extends ValidResponse, ErrorType extends InvalidResponse, ArgType extends RawArgType = RawArgType>(args?: ArgType, returnError = false): Promise<ResultType | ErrorType> {
    console.error(`Can't directly call PATCH. ${args}, ${returnError}. Use auto-generated path API.`);
    return callSammoAPI<ResultType, ErrorType>('patch', [], args, undefined, true);
}

export async function HEAD<ResultType extends ValidResponse, ArgType extends undefined = undefined>(args?: ArgType): Promise<ResultType>;
export async function HEAD<ResultType extends ValidResponse, ArgType extends undefined = undefined>(args: ArgType | undefined, returnError: false): Promise<ResultType>;
export async function HEAD<ResultType extends ValidResponse, ErrorType extends InvalidResponse, ArgType extends undefined = undefined>(args: ArgType | undefined, returnError: true): Promise<ResultType | ErrorType>;
export async function HEAD<ResultType extends ValidResponse, ErrorType extends InvalidResponse, ArgType extends undefined = undefined>(args?: ArgType, returnError = false): Promise<ResultType | ErrorType> {
    console.error(`Can't directly call HEAD. ${args}, ${returnError}. Use auto-generated path API.`);
    return callSammoAPI<ResultType, ErrorType>('head', [], args, undefined, true);
}

export async function DELETE<ResultType extends ValidResponse, ArgType extends RawArgType = RawArgType>(args?: ArgType): Promise<ResultType>;
export async function DELETE<ResultType extends ValidResponse, ArgType extends RawArgType = RawArgType>(args: ArgType | undefined, returnError: false): Promise<ResultType>;
export async function DELETE<ResultType extends ValidResponse, ErrorType extends InvalidResponse, ArgType extends RawArgType = RawArgType>(args: ArgType | undefined, returnError: true): Promise<ResultType | ErrorType>;
export async function DELETE<ResultType extends ValidResponse, ErrorType extends InvalidResponse, ArgType extends RawArgType = RawArgType>(args?: ArgType, returnError = false): Promise<ResultType | ErrorType> {
    console.error(`Can't directly call DELETE. ${args}, ${returnError}. Use auto-generated path API.`);
    return callSammoAPI<ResultType, ErrorType>('patch', [], args, undefined, true);
}