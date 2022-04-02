import type { ValidResponse, BettingInfo } from "@/defs";

export type BettingListResponse = ValidResponse & {
    bettingList: Record<number, Omit<BettingInfo & { totalAmount: number }, "candidates">>;
    year: number;
    month: number;
  };