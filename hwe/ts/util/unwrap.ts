import { Nullable } from "@util/Nullable";
import { NotNullExpected } from "@util/NotNullExpected";

export function unwrap<T>(result: Nullable<T>): T {
    if (result === null || result === undefined) {
        throw new NotNullExpected();
    }
    return result;
}
