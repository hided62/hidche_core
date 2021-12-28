import { InvalidResponse } from "@/defs";
import { callSammoAPI, ValidResponse } from "./callSammoAPI";

export type CurryCall<ResultType extends ValidResponse, ErrorType extends InvalidResponse> =
    ((args?: Record<string, unknown>) => Promise<ResultType>)
    | ((args: Record<string, unknown> | undefined, returnError: false) => Promise<ResultType>)
    | ((args: Record<string, unknown> | undefined, returnError: true) => Promise<ResultType | ErrorType>);

type SubValue<ResultType extends ValidResponse, ErrorType extends InvalidResponse> = CurryCall<ResultType, ErrorType> | { [property: string]: SubValue<ResultType, ErrorType> };

export function APIPathGen<ResultType extends ValidResponse, ErrorType extends InvalidResponse, T extends { [property: string]: SubValue<ResultType, ErrorType> },>(obj: T, path?: string[]): T {
    return new Proxy(obj, {
        get(target, key: string) {
            let nextPath: string[];
            if (path === undefined) {
                nextPath = [key];
            }
            else{
                nextPath = path;
                nextPath.push(key);
            }

            if (!(key in target)) {
                throw `${nextPath.join('/')} is not exists`;
            }

            const next = target[key];
            if (typeof (next) === 'function') {
                const callAPI: CurryCall<ResultType, ErrorType> = (args: Record<string, unknown> | undefined, returnError?: boolean) => {
                    if (returnError) {
                        return callSammoAPI<ResultType, ErrorType>(nextPath.join('/'), args, returnError);
                    }
                    return callSammoAPI<ResultType>(nextPath.join('/'), args, false);
                }
                return callAPI;
            }
            return APIPathGen(next, nextPath);
        }
    })
}