import type { ValidResponse } from "@/defs";

export type BettingListResponse = ValidResponse & {
  bettingList: Record<number, Omit<BettingInfo & { totalAmount: number }, "candidates">>;
  year: number;
  month: number;
};


export type BettingDetailResponse = ValidResponse & {
  bettingInfo: BettingInfo;
  bettingDetail: [string, number][];
  myBetting: [string, number][];
  remainPoint: number;
  year: number;
  month: number;
};

export type BettingInfo = {
  id: number;
  type: 'nationBetting',
  name: string;
  finished: boolean;
  selectCnt: number;
  isExclusive?: boolean;
  reqInheritancePoint: boolean;
  openYearMonth: number;
  closeYearMonth: number;
  candidates: Record<string, SelectItem>;
  winner?: number[];
}

export type SelectItem = {
  title: string;
  info?: string;
  isHtml?: boolean;
  aux?: Record<string, unknown>;
}
