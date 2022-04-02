import type { TurnObj } from "@/defs";

export type ReservedCommandResponse = {
    result: true;
    turnTime: string;
    turnTerm: number;
    year: number;
    month: number;
    date: string;
    turn: TurnObj[];
    autorun_limit: null | number;
  };