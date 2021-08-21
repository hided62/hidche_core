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
