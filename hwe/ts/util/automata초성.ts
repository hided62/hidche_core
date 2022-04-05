const convListLevel1: Record<string, Record<string, string>> = {
    'ㄱ': {
        'ㅅ': 'ㄳ',
    },
    'ㄴ': {
        'ㅈ': 'ㄵ',
        'ㅎ': 'ㄶ',
    },
    'ㄹ': {
        'ㅂ': 'ㄼ',
        'ㄱ': 'ㄺ',
        'ㅅ': 'ㄽ',
        'ㅁ': 'ㄻ',
        'ㅎ': 'ㅀ',
        'ㅌ': 'ㄾ',
        'ㅍ': 'ㄿ',
    },
    'ㅂ': {
        'ㅅ': 'ㅄ',
    },
}

const convListLevel2: Record<string, Record<string, string>> = {
    'ㄱ': {
        'ㄱ': 'ㄲ',
        'ㅅ': 'ㄳ',
    },
    'ㄴ': {
        'ㅈ': 'ㄵ',
        'ㅎ': 'ㄶ',
    },
    'ㄷ': {
        'ㄷ': 'ㄸ',
    },
    'ㄹ': {
        'ㅂ': 'ㄼ',
        'ㄱ': 'ㄺ',
        'ㅅ': 'ㄽ',
        'ㅁ': 'ㄻ',
        'ㅎ': 'ㅀ',
        'ㅌ': 'ㄾ',
        'ㅍ': 'ㄿ',
    },
    'ㅂ': {
        'ㅂ': 'ㅃ',
        'ㅅ': 'ㅄ',
    },
    'ㅅ': {
        'ㅅ': 'ㅆ',
    },
    'ㅈ': {
        'ㅈ': 'ㅉ',
    }
}

function automata초성(text: string, convList: Record<string, Record<string, string>>): string{
    const result: string[] = [];
    let head: undefined | string = undefined;
    for (const ch of text) {
        if (head === undefined) {
            if(!(ch in convList)){
                result.push(ch);
                continue;
            }
            head = ch;
            continue;
        }

        const nextConv = convList[head];
        if(ch in nextConv){
            result.push(nextConv[ch]);
            head = undefined;
            continue;
        }

        result.push(head);
        if(!(ch in convList)){
            result.push(ch);
            head = undefined;
            continue;
        }
        head = ch;
    }
    if(head !== undefined){
        result.push(head);
        head = undefined;
    }
    return result.join('');
}

export function automata초성All(text: string): [string, string]{
    return [automata초성(text, convListLevel1), automata초성(text, convListLevel2)];
}

export function automata초성Level1(text: string): string{
    return automata초성(text, convListLevel1);
}

export function automata초성Level2(text: string): string {
    return automata초성(text, convListLevel2);
}