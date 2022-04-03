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