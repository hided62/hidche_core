type SubValue<V extends (...args: unknown[]) => unknown> = V | { [property: string]: SubValue<V> };

export function APIPathGen<V extends () => unknown, T extends { [property: string]: SubValue<V> }>(obj: T, callback: (path: string[]) => V, path?: string[]): T {
    return new Proxy(obj, {
        get(target, key: string) {
            let nextPath: string[];
            if (path === undefined) {
                nextPath = [key];
            }
            else {
                nextPath = path;
                nextPath.push(key);
            }

            if (!(key in target)) {
                throw `${nextPath} is not exists`;
            }

            const next = target[key];
            if (typeof (next) === 'function') {
                return callback(nextPath);
            }
            return APIPathGen(next, callback, nextPath);
        }
    })
}