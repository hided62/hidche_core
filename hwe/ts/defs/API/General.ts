export type JoinArgs = {
  name: string;
  leadership: number;
  strength: number;
  intel: number;
  pic: boolean;
  character: string;
  inheritSpecial?: string;
  inheritTurntime?: number;
  inheritCity?: number;
  inheritBonusStat?: [number, number, number];
};

export type GeneralLogType = 'generalHistory'|'generalAction'|'battleResult'|'battleDetail';

export type GetGeneralLogResponse = {
  result: true,
  reqType: GeneralLogType,
  generalID: number,
  log: Record<string, string>
}