import bs from 'binary-search';

const defenceMap: [number,string][] = [
  [0, "△"],
  [60, "○"],
  [80, "◎"],
  [90, "☆"],
  [999, "×"],
]

export function formatDefenceTrain(defenceTrain: number): string {
  const idx = bs(defenceMap, defenceTrain, ([defenceKey], needle) => defenceKey - needle);
  if(idx >= 0){
    return defenceMap[idx][1]??'?';
  }
  const uidx = (~idx) - 1;
  return defenceMap[uidx][1]??'?';
}