import bs from "binary-search";

const refreshScoreMap: [number, string][] = [
  [0, "안함"],
  [50, "무관심"],
  [100, "보통"],
  [200, "가끔"],
  [400, "자주"],
  [800, "열심"],
  [1600, "중독"],
  [3200, "폐인"],
  [6400, "경고"],
  [12800, "헐..."],
];

export function formatRefreshScore(refreshScore: number) {
  const idx = bs(refreshScoreMap, refreshScore, ([key], needle) => key - needle);
  if (idx >= 0) {
    return refreshScoreMap[idx][1] ?? "?";
  }
  const uidx = (~idx) - 1;
  return refreshScoreMap[uidx][1];
}
