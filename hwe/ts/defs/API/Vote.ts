import type { ValidResponse } from "@/util/callSammoAPI";

export type VoteInfo = {
  id: number;
  title: string;
  multipleOptions: number;
  startDate: string;
  endDate?: string;
  options: string[];
}

export type VoteComment = {
  id: number;
  voteID: number;
  generalID: number;
  nationName: string;
  generalName: string;
  text: string;
  date: string;
}

export type VoteListResult = ValidResponse & {
  votes: Record<string, VoteInfo>;
}

export type VoteDetailResult = ValidResponse & {
  voteInfo: VoteInfo,
  votes: [number[], number][],
  comments: VoteComment[],
  myVote: null|number[],
  userCnt: number,
}