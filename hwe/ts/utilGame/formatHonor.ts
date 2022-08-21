import bs from 'binary-search';

const hornorMap: [number, string][] = [
  [0, '전무'],
  [640, '무명'],
  [2560, '신동'],
  [5760, '약간'],
  [10240, '평범'],
  [16000, '지역적'],
  [23040, '전국적'],
  [31360, '세계적'],
  [40960, '유명'],
  [45000, '명사'],
  [51840, '호걸'],
  [55000, '효웅'],
  [64000, '영웅'],
  [77440, '구세주'],
]

export function formatHonor(experience: number): string {
  const idx = bs(hornorMap, experience, ([experienceKey], needle) => experienceKey - needle);
  if (idx >= 0) {
    return hornorMap[idx][1] ?? '?';
  }
  const uidx = (~idx) - 1;
  return hornorMap[uidx][1] ?? '?';
}