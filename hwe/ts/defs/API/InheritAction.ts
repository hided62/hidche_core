import type { ValidResponse } from "@/util/callSammoAPI";

export type inheritBuffType =
  | "warAvoidRatio"
  | "warCriticalRatio"
  | "warMagicTrialProb"
  | "domesticSuccessProb"
  | "domesticFailProb"
  | "warAvoidRatioOppose"
  | "warCriticalRatioOppose"
  | "warMagicTrialProbOppose";

export type InheritPointLogItem = {
  id: number;
  server_id: string;
  year: number;
  month: number;
  date: string;
  text: string;
};

export type InheritLogResponse = ValidResponse & {
  log: InheritPointLogItem[];
};
