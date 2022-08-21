import { isString } from "lodash-es";

interface NameValuePair {
    name: string,
    value: string
}

export function mergeKVArray(array : NameValuePair[]):Record<string, string|string[]>{
    const result:Record<string, string|string[]> = {};

    for(const {name, value} of array){
        if(!isString(name)){
            throw new TypeError(`${name} is not string`);
        }
        if(!isString(value)){
            throw new TypeError(`${value} is not string`);
        }

        if(name === '' || name === '[]'){
            continue;
        }
        if(name.length > 2 && name.slice(-2) == '[]'){
            const keyHead = name.slice(0, -2);
            if(!(keyHead in result)){
                result[keyHead] = [value];
            }
            else{
                (result[keyHead] as string[]).push(value);
            }
            continue;
        }
        result[name] = value;
    }
    return result;
}