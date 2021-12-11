
/**
 * object의 array를 id를 key로 삼는 object로 재 변환
 */
export function convertDictById<K extends string | number, T extends { id: K }>(arr: ArrayLike<T>): Record<K, T> {
    const result: Record<string | number, T> = {};
    for (const v of Object.values(arr)) {
        result[v.id] = v;
    }
    return result;
}

/**
 * array를 set 형태의 object로 변환
 */
export function convertSet<K extends string | number>(arr: ArrayLike<K>): Record<K, true> {
    const result: Record<string | number, true> = {};
    for (const v of Object.values(arr)) {
        result[v] = true;
    }
    return result;
}

export function stringFormat(text: string, ...args: (string | number)[]): string {
    return text.replace(/{(\d+)}/g, function (match, number) {
        return (typeof args[number] != 'undefined') ? args[number].toString() : match;
    });
}

/**
 * 게임내에서 지원하는 color type만 선택할 수 있도록 해주는 함수
 * @param {string} color #AAAAAA 또는 AAAAAA 형태로 작성된 RGB hex color string
 * @returns {string}
 */
export function convColorValue(color: string): string {
    if (color.charAt(0) == '#') {
        color = color.substr(1);
    }
    color = color.toUpperCase();

    const colorBase = new Set([
        '000080', '0000FF', '008000', '008080', '00BFFF', '00FF00', '00FFFF', '20B2AA',
        '2E8B57', '483D8B', '6495ED', '7B68EE', '7CFC00', '7FFFD4', '800000', '800080',
        '808000', '87CEEB', 'A0522D', 'A9A9A9', 'AFEEEE', 'BA55D3', 'E0FFFF', 'F5F5DC',
        'FF0000', 'FF00FF', 'FF6347', 'FFA500', 'FFC0CB', 'FFD700', 'FFDAB9', 'FFFF00',
        'FFFFFF'
    ]);

    if (!colorBase.has(color)) {
        return '000000';
    }

    return color;
}


//linkify가 불러와 있어야함
declare global {
    interface Window {
        linkifyStr: (v: string, k?: Record<string, string | number>) => string;
    }
}
export function combineObject<K extends string, V>(item: V[], columnList: K[]): Record<K, V> {
    const newItem: Record<string, V> = {};
    for (const columnIdx in columnList) {
        const columnName = columnList[columnIdx];
        newItem[columnName] = item[columnIdx];
    }
    return newItem;
}

export function combineArray<K extends string, V>(array: V[][], columnList: K[]): Record<K, V>[] {
    const result: Record<K, V>[] = [];
    for (const key of array.keys()) {
        const item = array[key];
        result[key] = combineObject(item, columnList);
    }
    return result;
}


export function errUnknown(): void {
    alert('작업을 실패했습니다.');
}

/*
function br2nl (text) {
    return text.replace(/<\s*\/?br\s*[\/]?>/gi, '\n');
}
*/

export function getNpcColor(npcType: number): 'cyan' | 'skyblue' | null {
    if (npcType >= 2) {
        return 'cyan';
    }
    if (npcType == 1) {
        return 'skyblue';
    }
    return null;
}
