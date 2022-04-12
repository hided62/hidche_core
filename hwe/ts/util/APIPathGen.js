export function APIPathGen(obj, callback, path, pathParams) {
    return new Proxy(obj, {
        get(target, key) {
            let nextPath;
            if (path === undefined) {
                nextPath = [key.toString()];
            }
            else {
                nextPath = [...path, key.toString()];
            }

            if (pathParams !== undefined) {
                pathParams = { ...pathParams };
            }

            const varType = target.__nextVarType;
            let varKey = target.__nextVarKey;
            let next;
            if (varType !== undefined && varKey !== undefined) {
                if(varType == 'number'){
                    if(key != Number(key)){
                        throw `${key} is not ${varType}`;
                    }
                    key = Number(key);
                }
                else if ((typeof key) !== varType) {
                    throw `${key} is not ${varType}, but ${typeof key}`;
                }
                if(pathParams === undefined){
                    pathParams = {}
                }
                pathParams[varKey] = key;
                nextPath.pop();
                next = target.next;
            }
            else if (key in target) {
                next = target[key];
            }
            else {
                throw `${nextPath} is not exists`;
            }

            if (typeof (next) === 'function') {
                return callback(nextPath, next, pathParams);
            }
            return APIPathGen(next, callback, nextPath, pathParams);
        }
    })
}

//generic 인자로 '자동'을 주려면 생략해야하므로 2단 호출
export function StrVar(key) {
    return (next) => {
        return {
            __nextVarType: 'string',
            __nextVarKey: key,
            next
        }
    }
}

export function NumVar(key, next) {
    return {
        __nextVarType: 'number',
        __nextVarKey: key,
        next
    }
}