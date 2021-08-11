type ErrType<T> = { new(msg?: string): T }
type Nullable<T> = T | null | undefined

export class RuntimeError extends Error {
    public name = 'RuntimeError';
    constructor(public message: string = '') {
        super(message);
    }
    toString(): string {
        if (this.message) {
            return this.name + ': ' + this.message;
        }
        else {
            return this.name;
        }
    }
}

export class NotNullExpected extends RuntimeError {
    public name = 'NotNullExpected';
}

export function unwrap<T>(result: Nullable<T>): T {
    if (result === null || result === undefined) {
        throw new NotNullExpected();
    }
    return result;
}

export function unwrap_err<T, ErrT extends Error>(result: Nullable<T>, errType: ErrType<ErrT>, errMsg?: string): T {
    if (result === null || result === undefined) {
        throw new errType(errMsg);
    }
    return result;
}