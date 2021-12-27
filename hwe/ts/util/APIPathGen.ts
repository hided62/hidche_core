type SubValue = string | { [property: string]: SubValue };

const hasKey = <T extends Record<string | symbol, unknown>>(obj: T, k: string | symbol | number): k is keyof T =>
    k in obj;

export function APIPathGen<K extends string, V extends SubValue>(obj: Record<K, V>, path?: string[]): Record<K, V> {
    return new Proxy(obj, {
        get(target, key: K) {
            if (path === undefined) {
                path = [key];
            }
            else {
                path.push(key);
            }


            if (hasKey(target, key)) {
                const next: V = target[key];
                if (typeof (next) === 'string') {
                    return path.join('/');
                }
                if (typeof (next) === 'object') {
                    return APIPathGen(next, path);
                }
                throw 'unknown';
            }
            throw `${path.join('/')} is not exists`;
        }
    })
}