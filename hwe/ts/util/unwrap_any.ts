import { Nullable } from "./Nullable";
import { NotNullExpected } from "./NotNullExpected";


export function unwrap_any<T>(result: Nullable<unknown>): T {
    if (result === null || result === undefined) {
        throw new NotNullExpected();
    }
    return result as T;
}
