import { IDItem } from '@/defs';

export function convertIDArray<T>(array: Iterable<T>): IDItem<T>[] {
    const result: IDItem<T>[] = [];
    for (const id of array) {
        result.push({ id });
    }
    return result;
}