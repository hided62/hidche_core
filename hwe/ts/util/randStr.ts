const charList = [
    '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
    'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
    'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't',
    'u', 'v', 'w', 'x', 'y', 'z',
];

export function randStr(len: number): string {
    const result = [];
    const charListLen = charList.length;
    let isStart = true;

    while(len > 0){
        const randChrIdx = Math.floor(Math.random() * charListLen);
        if(isStart){
            if(randChrIdx == 0){
                continue;
            }
            isStart = false;
        }
        result.push(charList[randChrIdx]);
        len -= 1;
    }
    return result.join('');
}