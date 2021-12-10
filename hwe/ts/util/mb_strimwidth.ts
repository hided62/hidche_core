import { mb_strwidth } from "@util/mb_strwidth";

/**
 * mb_strimwidth
 * @param String
 * @param int
 * @param int
 * @param String
 * @return String
 * @see http://www.php.net/manual/function.mb-strimwidth.php
 */
export function mb_strimwidth(str: string, start: number, width: number, trimmarker = ''): string {
    const trimmakerWidth = mb_strwidth(trimmarker);
    const l = str.length;
    let trimmedLength = 0;
    const trimmedStr: string[] = [];
    for (let i = start; i < l; i++) {
        const c = str.charAt(i);
        const charWidth = mb_strwidth(c);
        const next = str.charAt(i + 1);
        const nextWidth = mb_strwidth(next);

        trimmedLength += charWidth;
        trimmedStr.push(c);
        if (trimmedLength + trimmakerWidth + nextWidth > width) {
            trimmedStr.push(trimmarker);
            break;
        }
    }
    return trimmedStr.join('');
}
