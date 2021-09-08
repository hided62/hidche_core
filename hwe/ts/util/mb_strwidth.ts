//TODO: X-Requested-With 믿지 말자.
//https://gist.github.com/demouth/3217440
/**
 * mb_strwidth
 * @see http://php.net/manual/function.mb-strwidth.php
 */
export function mb_strwidth(str: string): number {
    const l = str.length;
    let length = 0;
    for (let i = 0; i < l; i++) {
        const c = str.charCodeAt(i);
        if (0x0000 <= c && c <= 0x0019) {
            length += 0;
        } else if (0x0020 <= c && c <= 0x1FFF) {
            length += 1;
        } else if (0x2000 <= c && c <= 0xFF60) {
            length += 2;
        } else if (0xFF61 <= c && c <= 0xFF9F) {
            length += 1;
        } else if (0xFFA0 <= c) {
            length += 2;
        }
    }
    return length;
}
