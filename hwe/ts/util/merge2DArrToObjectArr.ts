
import type { ValuesOf } from "@/defs";
import { zip } from "lodash-es";

export function merge2DArrToObjectArr<T extends Record<string, unknown>>(column: (keyof T)[], list: ValuesOf<T>[][]): T[]{
    const result: T[] = [];
    for(const rawItem of list){
        const item: Record<string, unknown> = {};

        if(column.length != rawItem.length){
            throw `column과 item의 길이가 같지 않습니다: ${column.length} != ${list.length}, ${JSON.stringify(item)}`;
        }

        for(const [key, value] of zip(column, rawItem)){
            item[key as string] = value;
        }
        result.push(item as T);
    }
    return result;
}