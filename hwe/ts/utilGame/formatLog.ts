const regex = /<([RBGMCLSODYW]1?|1|\/)>/g;

//TODO: <R>에서 더 확장해서, <R|G>, <R|N> 형태로 뒤에 타입을 지정할 수 있도록 한다.

const convertMap: Record<string, string> = {
  "R": 'color: red;',
  "B": 'color: blue;',
  "G": 'color: green;',
  "M": 'color: magenta;',
  "C": 'color: cyan;',
  "L": 'color: limegreen;',
  "S": 'color: skyblue;',
  "O": 'color: orangered;',
  "D": 'color: orangered;',
  "Y": 'color: yellow;',
  "W": 'color: white;',
  "1": 'font-size: 0.9em;',
}

const convertMap2: Record<string, string> = {
  "1": 'font-size: 0.9em;',
}

export function formatLog(text?: string): string {
  if (!text) {
    return '';
  }

  let matchRes;
  let lastIndex = 0;
  const result = [];
  while ((matchRes = regex.exec(text)) !== null) {
    const {
      0: partAll,
      1: subPart,
      index,
    } = matchRes;
    if (lastIndex != index) {
      result.push(text.slice(lastIndex, index));
    }

    if (subPart == '/') {
      result.push(`</span>`);
    }
    else if (subPart.length == 2) {
      result.push(`<span style="${convertMap[subPart[0]] ?? ''}${convertMap2[subPart[1]] ?? ''}">`);
    }
    else {
      result.push(`<span style="${convertMap[subPart] ?? ''}">`);
    }

    lastIndex = index + partAll.length;
  }

  if (lastIndex != text.length) {
    result.push(text.slice(lastIndex));
  }

  return result.join('');
}