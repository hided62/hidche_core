export function APIPathGen(obj, callback, path) {
    return new Proxy(obj, {
        get(target, key) {
            let nextPath;
            if (path === undefined) {
                nextPath = [key.toString()];
            }
            else {
                nextPath = [...path, key.toString()];
            }

            const varType = target.__nextVarType;
            let next;
            if (varType !== undefined) {
                if (typeof key !== varType) {
                    throw `${key} is not ${varType}`;
                }
                next = target.next;
            }
            else if (key in target) {
                next = target[key];
            }
            else {
                throw `${nextPath} is not exists`;
            }

            if (typeof (next) === 'function') {
                return callback(nextPath);
            }
            return APIPathGen(next, callback, nextPath);
        }
    })
}

//generic 인자로 '자동'을 주려면 생략해야하므로 2단 호출
export function StrVar() {
    return (next) => {
        return {
            __nextVarType: 'string',
            next
        }
    }
}

export function NumVar(next) {
    return {
        __nextVarType: 'number',
        next
    }
}