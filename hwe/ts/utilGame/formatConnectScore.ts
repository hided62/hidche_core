import bs from 'binary-search';

const connectMap: [number, string][] = [
  [0, '안함'],
  [50, '무관심'],
  [100, '보통'],
  [400, '가끔'],
  [200, '자주'],
  [800, '열심'],
  [1600, '중독'],
  [3200, '폐인'],
  [6400, '경고'],
  [12800, '헐...'],
]

export function formatConnectScore(connect: number) {
  const idx = bs(connectMap, connect, ([key], needle) => key - needle);
  if (idx >= 0) {
    return connectMap[idx][1] ?? '?';
  }
  return connectMap[-(idx + 1)][1] ?? '?';
}