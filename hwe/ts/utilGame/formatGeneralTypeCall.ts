import type { GameConstType } from "@/defs/GameObj";

export function formatGeneralTypeCall(leadership: number, strength: number, intel: number, gameConst: GameConstType): string {
  if (leadership < 40) {
    if (strength + intel < 40) {
      return '아둔';
    }
    if (intel >= gameConst.chiefStatMin && strength < intel * 0.8) {
      return '학자';
    }
    if (strength >= gameConst.chiefStatMin && intel < strength * 0.8) {
      return '장사';
    }
    return '명사';
  }

  const maxStat = Math.max(leadership, strength, intel);
  const sum2Stat = Math.min(leadership + strength, strength + intel, intel + leadership);
  if (maxStat >= gameConst.chiefStatMin + gameConst.statGradeLevel && sum2Stat >= maxStat * 1.7) {
    return '만능';
  }
  if (strength >= gameConst.chiefStatMin - gameConst.statGradeLevel && intel < strength * 0.8) {
    return '용장';
  }
  if (intel >= gameConst.chiefStatMin - gameConst.statGradeLevel && strength < intel * 0.8) {
    return '명장';
  }
  if (leadership >= gameConst.chiefStatMin - gameConst.statGradeLevel && strength + intel < leadership) {
    return '차장';
  }
  return '평범';
}