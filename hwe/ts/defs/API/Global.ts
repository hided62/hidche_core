import type {
  CityID,
  CrewTypeID,
  GameCityDefault,
  GameConstType,
  GameUnitType,
  GameIActionKey,
  GameIActionCategory,
  GameIActionInfo,
} from "@/defs/GameObj";
import type { diplomacyState, MapResult, NationLevel, SimpleNationObj } from "@/defs";
import type { GeneralListItemP0, GeneralListItemP1, NationNotice } from "./Nation";
import type { VoteInfo } from "./Vote";

export interface GetConstResponse {
  result: true;
  cacheKey: string;
  data: {
    gameConst: GameConstType;
    gameUnitConst: Record<CrewTypeID, GameUnitType>;
    cityConst: Record<CityID, GameCityDefault>;
    cityConstMap: {
      region: Record<number | string, string | number>;
      level: Record<number | string, string | number>; //defs.CityLevelText
    };
    iActionInfo: Record<GameIActionCategory, Record<GameIActionKey, GameIActionInfo>>;
    iActionKeyMap: {
      availableNationType: "nationType";
      neutralNationType: "nationType";
      defaultSpecialDomestic: "specialDomestic";
      availableSpecialDomestic: "specialDomestic";
      optionalSpecialDomestic: "specialDomestic";
      defaultSpecialWar: "specialWar";
      availableSpecialWar: "specialWar";
      optionalSpecialWar: "specialWar";
      neutralPersonality: "personality";
      availablePersonality: "personality";
      optionalPersonality: "personality";
      allItems: "item";
    };
    version: string;
  };
}

export type HistoryObj = {
  server_id: string;
  year: number;
  month: number;
  map: MapResult;
  global_history: string[];
  global_action: string[];
  nations: {
    capital: number;
    cities: string[];
    color: string;
    gennum: number;
    level: number;
    name: string;
    nation: number;
    power: number;
    type: GameIActionKey;
  }[];
};

export type GetHistoryResponse = {
  result: true;
  data: HistoryObj & {
    hash: string;
    //no: number,
  };
};

export type GetCurrentHistoryResponse = {
  result: true;
  data: HistoryObj;
};

export type GetDiplomacyResponse = {
  result: true;
  nations: SimpleNationObj[];
  conflict: [number, Record<number, number>][];
  diplomacyList: Record<number, Record<number, diplomacyState>>;
  myNationID: number;
};

export type ExecuteResponse = {
  result: true;
  updated: boolean;
  locked: boolean;
  lastExecuted: string;
};

export type GetRecentRecordResponse = {
  result: true;
  history: [number, string][];
  global: [number, string][];
  general: [number, string][];
  flushHistory: 1 | 0;
  flushGlobal: 1 | 0;
  flushGeneral: 1 | 0;
};

export type AutorunUserMode = "develop" | "warp" | "recruit" | "recruit_high" | "train" | "battle" | "chief";

export type GetFrontInfoResponse = {
  result: true;
  recentRecord: Omit<GetRecentRecordResponse, "result">;
  global: {
    scenarioText: string;
    extendedGeneral: 1 | 0;
    isFiction: 1 | 0;
    npcMode: 2 | 1 | 0;
    joinMode: "onlyRandom" | "full";
    startyear: number;
    year: number;
    month: number;
    autorunUser: {
      limit_minutes: number,
      options: Record<AutorunUserMode, number>,
    };
    turnterm: number;
    lastExecuted: string;
    lastVoteID: number;
    develCost: number;
    noticeMsg: number;
    onlineNations: string | null; //TODO: string[]으로 변경
    onlineUserCnt: number | null;
    apiLimit: number;
    auctionCount: number;
    isTournamentActive: boolean;
    isTournamentApplicationOpen: boolean;
    isBettingActive: boolean;
    isLocked: boolean;
    tournamentType: null | 0 | 1 | 2 | 3;
    tournamentState: 0 | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10;
    tournamentTime: string;
    genCount: [number, number][];
    generalCntLimit: number;
    serverCnt: number;
    lastVote: VoteInfo | null;
  };
  general: GeneralListItemP1 & {
    permission: number;
    troopInfo?: {
      leader: {
        city: number;
        reservedCommand: GeneralListItemP1["reservedCommand"];
      };
      name: string;
    };
    impossibleUserAction: [string, number][];
  };
  nation: {
    id: number;
    name: string;
    population: {
      cityCnt: number;
      now: number;
      max: number;
    };
    crew: {
      generalCnt: number;
      now: number;
      max: number;
    };
    type: {
      raw: GameIActionKey;
      name: string;
      pros: string;
      cons: string;
    };
    color: string;
    level: NationLevel
    capital: number;
    gold: number;
    rice: number;
    tech: number;
    gennum: number;
    power: number;
    bill: number;
    taxRate: number;
    onlineGen: string;
    notice: NationNotice | null;
    topChiefs: Record<
      11 | 12,
      {
        officer_level: number;
        no: number;
        name: string;
        npc: GeneralListItemP0["npc"];
      }
    >;
    diplomaticLimit: number;
    strategicCmdLimit: number;
    impossibleStrategicCommand: [string, number][];
    prohibitScout: 1 | 0;
    prohibitWar: 1 | 0;
  };
  city: {
    id: number;
    name: string;
    nationInfo: {
      id: number;
      name: string;
      color: string;
    };
    level: number;
    trust: number;
    pop: [number, number];
    agri: [number, number];
    comm: [number, number];
    secu: [number, number];
    def: [number, number];
    wall: [number, number];
    trade: null | number;
    officerList: Record<
      2 | 3 | 4,
      {
        officer_level: 2 | 3 | 4;
        name: string;
        npc: GeneralListItemP0["npc"];
      } | null
    >;
  };
  aux: {
    myLastVote?: number;
  };
};


export type MenuLine = {
  type: 'line';
}

export type MenuItem = {
  type: 'item';
  name: string;
  url: string;
  funcCall?: string;
  icon?: string;
  newTab?: boolean;
  condHightlightVar?: string;
  condShowVar?: string;
}

export type MenuSplit = {
  type: 'split';
  main: MenuItem;
  subMenu: (MenuItem | MenuLine)[];
}

export type MenuMulti = {
  type: 'multi';
  name: string;
  subMenu: (MenuItem | MenuLine)[];
}


export type GetMenuResponse = {
  result: true;
  menu: (MenuItem | MenuSplit | MenuMulti)[];
}