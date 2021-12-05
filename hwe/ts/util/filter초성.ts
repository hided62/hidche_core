export function filter초성(text: string): string {
    const 초성 = [
        "ㄱ", "ㄲ", "ㄴ", "ㄷ", "ㄸ", "ㄹ", "ㅁ", "ㅂ", "ㅃ",
        "ㅅ", "ㅆ", "ㅇ", "ㅈ", "ㅉ", "ㅊ", "ㅋ", "ㅌ", "ㅍ", "ㅎ"
    ];
    const result: string[] = [];
    for (const char of text) {
        const code = (char.codePointAt(0) ?? 0) - 44032;
        if (0 <= code && code < 11172) {
            result.push(초성[~~(code / 588)]);
        }
        else {
            result.push(char);
        }
    }
    return result.join('');
}