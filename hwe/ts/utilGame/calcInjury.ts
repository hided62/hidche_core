import type { GeneralListItemP0 } from "@/defs/API/Nation";

export function calcInjury(statKey: 'leadership'|'strength'|'intel', general: GeneralListItemP0){
  const baseStat = general[statKey];
  return Math.round(baseStat * (100 -general.injury) / 100);
}