export function APIPathGen(obj, callback, path) {
    return new Proxy(obj, {
        get(target, key) {
            if(typeof key === 'number'){
                key = key.toString();
            }
            else if(typeof key !== 'string'){
                throw `${key} is not string`;
            }
            let nextPath;
            if (path === undefined) {
                nextPath = [key];
            }
            else {
                nextPath = [...path, key];
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

export function StrVar(){
    return (next)=>next;
}