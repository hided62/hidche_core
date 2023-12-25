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

export type UserActionItem = {
  command: string,
  brief: string,
  untilYearMonth?: number,
};

type UserAction = {
  reserved?: Record<number, UserActionItem>,
  active?: UserActionItem[],
}

export type ReservedUserActionResponse = {
  result: true,
  userActions: UserAction,
}