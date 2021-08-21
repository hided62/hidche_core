import { Nullable } from "./Nullable";
import { NotNullExpected } from "./NotNullExpected";

export function unwrap<T>(result: Nullable<T>): T {
    if (result === null || result === undefined) {
        throw new NotNullExpected();
    }
    return result;
}
