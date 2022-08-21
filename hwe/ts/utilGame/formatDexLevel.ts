import bs from 'binary-search';

export const DexLevelMap: [number, string, string][] = [
  [0, 'navy', 'F-'],
  [350, 'navy', 'F'],
  [1375, 'navy', 'F+'],
  [3500, 'skyblue', 'E-'],
  [7125, 'skyblue', 'E'],
  [12650, 'skyblue', 'E+'],
  [20475, 'seagreen', 'D-'],
  [31000, 'seagreen', 'D'],
  [44625, 'seagreen', 'D+'],
  [61750, 'teal', 'C-'],
  [82775, 'teal', 'C'],
  [108100, 'teal', 'C+'],
  [138125, 'limegreen', 'B-'],
  [173250, 'limegreen', 'B'],
  [213875, 'limegreen', 'B+'],
  [260400, 'darkorange', 'A-'],
  [313225, 'darkorange', 'A'],
  [372750, 'darkorange', 'A+'],
  [439375, 'tomato', 'S-'],
  [513500, 'tomato', 'S'],
  [595525, 'tomato', 'S+'],
  [685850, 'darkviolet', 'Z-'],
  [784875, 'darkviolet', 'Z'],
  [893000, 'darkviolet', 'Z+'],
  [1010625, 'gold', 'EX-'],
  [1138150, 'gold', 'EX'],
  [1275975, 'white', 'EX+'],
];

export type DexInfo = {
  level: number,
  name: string,
  color: string,
}

export function formatDexLevel(dex: number): DexInfo {
  const rawIdx = bs(DexLevelMap, dex, ([dexKey], needle) => dexKey - needle);
  const level = rawIdx >= 0 ? rawIdx : (~rawIdx) - 1;

  const [, color, name] = DexLevelMap[level];

  return {
    level,
    name,
    color
  };
}