import type { ValidResponse } from "@/util/callSammoAPI";

export type BasicResourceAuctionBidder = {
  amount: number;
  date: string;
  generalID: number;
  generalName: string;
};

export type BasicResourceAuctionInfo = {
  id: number;
  type: "buyRice" | "sellRice";
  hostGeneralID: number;
  hostName: string;
  openDate: string;
  closeDate: string;
  amount: number;
  startBidAmount: number;
  finishBidAmount: number;
  highestBid: BasicResourceAuctionBidder;
};

export type UniqueItemAuctionBidder = {
  generalName: string;
  amount: number;
  isCallerHighestBidder: boolean;
  date: string;
};

export type UniqueItemAuctionInfo = {
  id: number;
  finished: boolean;
  title: string;
  target: string;
  isCallerHost: boolean;
  hostName: string;
  closeDate: string;
  remainCloseDateExtensionCnt: number;
  availableLatestBidCloseDate: string;
};

export type ActiveResourceAuctionList = ValidResponse & {
  buyRice: BasicResourceAuctionInfo[];
  sellRice: BasicResourceAuctionInfo[];
  recentLogs: string[];
  generalID: number;
};

export type UniqueItemAuctionList = ValidResponse & {
  list: (UniqueItemAuctionInfo & {
    highestBid: UniqueItemAuctionBidder;
  })[];
  obfuscatedName: string;
};

export type UniqueItemAuctionDetail = ValidResponse & {
  auction: UniqueItemAuctionInfo;
  bidList: UniqueItemAuctionBidder[];
  obfuscatedName: string;
  remainPoint: number;
};

export type OpenAuctionResponse = ValidResponse & {
  auctionID: number;
};
