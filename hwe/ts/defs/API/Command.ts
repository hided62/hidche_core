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

export type ReserveCommandResponse = {
  result: true,
  brief: string,
}

export type ReserveBulkCommandResponse = {
  result: true,
  briefList: string[],
}
