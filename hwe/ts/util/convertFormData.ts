import { isArray, isString, isNumber, isBoolean } from "lodash-es";

export function convertFormData(values: Record<string, null | number[] | string[] | boolean[] | number | string | boolean>): FormData {
    const formData = new FormData();

    const simpleConv = (v: unknown, key: string): string => {
        if (isString(v)) {
            return v;
        }
        if (isNumber(v)) {
            return v.toString();
        }
        if (isBoolean(v)) {
            return v ? 'true' : 'false';
        }
        if (v === null) {
            return '';
        }
        throw new TypeError(`지원하지 않는 formData Type: ${key}`);
    }

    for (const [key, value] of Object.entries(values)) {
        if (isArray(value)) {
            const arrKey = `${key}[]`;
            for (const subValue of value) {
                formData.append(arrKey, simpleConv(subValue, key));
            }
            continue;
        }

        formData.append(key, simpleConv(value, key));
    }

    return formData;
}