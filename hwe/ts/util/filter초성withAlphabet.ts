export function filter초성withAlphabet(text: string): [string, string] {
    const 초성 = [
        "ㄱ", "ㄲ", "ㄴ", "ㄷ", "ㄸ", "ㄹ", "ㅁ", "ㅂ", "ㅃ",
        "ㅅ", "ㅆ", "ㅇ", "ㅈ", "ㅉ", "ㅊ", "ㅋ", "ㅌ", "ㅍ", "ㅎ"
    ];
    const alphabets = [
        "r", "R", "s", "e", "E", "f", "a", "q", "Q",
        "t", "T", "d", "w", "W", "c", "z", "x", "v", "g"
    ];
    const resultH: string[] = [];
    const resultA: string[] = [];
    for (const char of text) {
        const code = (char.codePointAt(0) ?? 0) - 44032;
        if (0 <= code && code < 11172) {
            resultH.push(초성[~~(code / 588)]);
            resultA.push(alphabets[~~(code / 588)]);
        }
        else {
            resultH.push(char);
            resultA.push(char);
        }
    }
    return [resultH.join(''), resultA.join('')];
}