export function APIPathGen(obj, callback, path) {
    return new Proxy(obj, {
        get(target, key) {
            let nextPath;
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