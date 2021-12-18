import { combineObject } from "../common_legacy";


export function combineArray<K extends string, V>(array: V[][], columnList: K[]): Record<K, V>[] {
    const result: Record<K, V>[] = [];
    for (const key of array.keys()) {
        const item = array[key];
        result[key] = combineObject(item, columnList);
    }
    return result;
}
