export type MsgType = "private" | "public" | "national" | "diplomacy";

export const minMsgSeq: Record<MsgType, number> = {
  private: 0x7fffffff,
  public: 0x7fffffff,
  national: 0x7fffffff,
  diplomacy: 0x7fffffff,
};

export type MsgTarget = {
  id: number;
  name: string;
  nation_id: number; //XXX: 왜 이 값은 nationID가 아니고 nation_id인가?
  nation: string;
  color: string;
  icon: string;
};

export type MsgActionType = 'scout' | 'noAggression' | 'cancelNA' | 'stopWar';

export type MsgItem = {
  id: number;
  msgType: MsgType;
  src: MsgTarget;
  dest?: MsgTarget;
  text: string;
  option: {
    action?: MsgActionType;
    invalid?: boolean;
    deletable?: boolean;
    overwrite?: number[];
    hide?: boolean;
    silence?: boolean;
    delete?: number;
  };
  time: string;
};

export type MsgPrintItem = MsgItem & {
  generalName: string;
  nationID: number;
  nationType: "local" | "src" | "dest";
  myGeneralID: number;
  allowButton: boolean;
  last5min: string;
  now: string;
  invalidType: "msg_invalid" | "msg_valid";
  deletable: boolean;
  src: MsgTarget & { colorType: "bright" | "dark" };
  dest: MsgTarget & { colorType: "bright" | "dark" };
  defaultIcon: string;
};

export type MsgResponse = {
  [v in MsgType]: MsgItem[];
} & {
  result: true;
  nationID: number;
  generalName: string;
  sequence: number;
};

export type MailboxItem = {
  id: number,
  mailbox: number,
  color: string,
  name: string,
  nationID: number,
  //nation: string,
  general: [number, string, number][]
}

export type MabilboxListResponse = {
  result: true,
  nation: MailboxItem[]
}