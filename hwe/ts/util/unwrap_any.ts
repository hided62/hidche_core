import { Nullable, NotNullExpected } from "../util";


export function unwrap_any<T>(result: Nullable<unknown>): T {
    if (result === null || result === undefined) {
        throw new NotNullExpected();
    }
    return result as T;
}
