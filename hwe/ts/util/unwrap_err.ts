import { Nullable } from "@util/Nullable";

type ErrType<T> = { new(msg?: string): T }

export function unwrap_err<T, ErrT extends Error>(result: Nullable<T>, errType: ErrType<ErrT>, errMsg?: string): T {
    if (result === null || result === undefined) {
        throw new errType(errMsg);
    }
    return result;
}
