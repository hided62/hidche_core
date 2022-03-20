import type { Nullable } from "@util/Nullable";
import { NotNullExpected } from "@util/NotNullExpected";


export function unwrap_any<T>(result: Nullable<unknown>): T {
    if (result === null || result === undefined) {
        throw new NotNullExpected();
    }
    return result as T;
}
