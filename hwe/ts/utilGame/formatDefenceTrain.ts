import bs from 'binary-search';
import { clamp } from 'lodash-es';

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
  const uidx = clamp(-idx - 1, 0, defenceMap.length - 1);
  return defenceMap[uidx][1]??'?';
}