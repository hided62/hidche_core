import type { Args } from "@/processing/args";
import type { ValidResponse, InvalidResponse } from "@/util/callSammoAPI";
import type { GameIActionKey, GameObjClassKey } from "./GameObj";

export type { ValidResponse, InvalidResponse };
export type BasicGeneralListResponse = {
    result: true,
    nationID: number,
    generalID: number,
    column: ['no', 'name', 'npc'],
    list: Record<string, [number, string, number][]>,
    nation: Record<string, {
        nation: number,
        name: string,
        color: string,
    }>
}


export type PublicGeneralItem = {
    no: number,
    picture: string,
    imgsvr: 0 | 1,
    npc: number,
    age: number,
    nation: string,
    specialDomestic: GameObjClassKey,
    specialWar: GameObjClassKey,
    personal: GameObjClassKey,
    name: string,
    ownerName: string | null,
    injury: number,
    leadership: number,
    lbonus: number,
    strength: number,
    intel:  number,
    explevel:  number,
    experienceStr: string,
    dedicationStr: string,
    officerLevelStr: string,
    killturn: number,
    connect: number,
}


export type GeneralListResponse = {
    result: true,
    list: [
        PublicGeneralItem['no'],
        PublicGeneralItem['picture'], PublicGeneralItem['imgsvr'],
        PublicGeneralItem['npc'], PublicGeneralItem['age'], PublicGeneralItem['nation'],
        PublicGeneralItem['specialDomestic'], PublicGeneralItem['specialWar'], PublicGeneralItem['personal'],
        PublicGeneralItem['name'], PublicGeneralItem['ownerName'],
        PublicGeneralItem['injury'],
        PublicGeneralItem['leadership'], PublicGeneralItem['lbonus'], PublicGeneralItem['strength'], PublicGeneralItem['intel'],
        PublicGeneralItem['explevel'],
        PublicGeneralItem['experienceStr'], PublicGeneralItem['dedicationStr'], PublicGeneralItem['officerLevelStr'],
        PublicGeneralItem['killturn'], PublicGeneralItem['connect']
    ][],
    token?: Record<number, number>,
}

export type NationLevel = 0 | 1 | 2 | 3 | 4 | 5 | 6 | 7;
export const NationLevelText: Record<NationLevel, string> = {
    0: '?????????',
    1: '??????',
    2: '??????',
    3: '?????????',
    4: '??????',
    5: '???',
    6: '???',
    7: '??????',
}

export type CityLevel = 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8;
export const CityLevelText: Record<CityLevel, string> = {
    1: '???',
    2: '???',
    3: '???',
    4: '???',
    5: '???',
    6: '???',
    7: '???',
    8: '???'
}

export type NationStaticItem = {
    nation: number,
    name: string,
    color: string,
    type: string,
    level: NationLevel,
    capital: number,
    gennum: number,
    power: number,
}

export type NPCGeneralActions =
    'NPC????????????' |
    '??????' |
    '????????????' |
    '??????' |
    '????????????' |
    '????????????' |
    '????????????' |
    //'NPC??????'|
    'NPC??????' |
    '??????' |
    '????????????' |
    '????????????' |
    '????????????' |
    '????????????' |
    '????????????';

export type NPCChiefActions =
    '???????????????' |
    '????????????' |
    '??????' |
    '?????????????????????' |
    '??????????????????' |
    '?????????????????????' |
    '?????????????????????' |
    '???????????????????????????' |
    '?????????????????????' |
    '???????????????' |
    //'???????????????'|
    '??????????????????' |
    '??????????????????' |
    'NPC????????????' |
    'NPC????????????' |
    'NPC????????????' |
    'NPC??????' |
    'NPC????????????' |
    '?????????????????????' |
    'NPC????????????' |
    'NPC??????';

export type NationPolicy = {
    reqNationGold: number,
    reqNationRice: number,
    CombatForce: Record<number, number[]>,
    SupportForce: number[],
    DevelopForce: number[],
    reqHumanWarUrgentGold: number,
    reqHumanWarUrgentRice: number,
    reqHumanWarRecommandGold: number,
    reqHumanWarRecommandRice: number,
    reqHumanDevelGold: number,
    reqHumanDevelRice: number,
    reqNPCWarGold: number,
    reqNPCWarRice: number,
    reqNPCDevelGold: number,
    reqNPCDevelRice: number,
    minimumResourceActionAmount: number,
    maximumResourceActionAmount: number,
    minNPCWarLeadership: number,
    minWarCrew: number,
    minNPCRecruitCityPopulation: number,
    safeRecruitCityPopulationRatio: number,
    properWarTrainAtmos: number,
    cureThreshold: number,
}

export type ItemTypeKey = 'horse' | 'weapon' | 'book' | 'item';
export const ItemTypeNameMap: Record<ItemTypeKey, string> = {
    horse: '??????',
    weapon: '??????',
    book: '??????',
    item: '??????',
}

export declare type Colors = 'primary' | 'secondary' | 'success' | 'danger' | 'warning' | 'info' | 'dark' | 'light';

export type IDItem<T> = {
    id: T;
};

export type ToastType = {
    content: {
        title?: string,
        body?: string,
    },
    options?: {
        variant?: Colors,
        delay?: number,
    }
}

export const keyScreenMode = 'sam.screenMode';
export type ScreenModeType = 'auto' | '500px' | '1000px';

export declare type ValuesOf<T> = T[keyof T];

export const NoneValue = 'None' as const;

export type Optional<Type> = {
    [Property in keyof Type]+?: Type[Property];
};

export type OptionalFull<Type> = {
    [Property in keyof Type]: Type[Property] | undefined;
};

export type TurnObj = {
    action: string;
    brief: string;
    arg: Args;
};


export type CommandItem = {
    value: string;
    title: string;
    info: string,
    compensation: number;
    simpleName: string;
    possible: boolean;
    reqArg: boolean;
    searchText?: string;
};

type diplomacyInfo = {
    name: string,
    color?: string,
}
export type diplomacyState = 0 | 1 | 2 | 7;
export const diplomacyStateInfo: Record<diplomacyState, diplomacyInfo> = {
    0: { name: '??????', color: 'red' },
    1: { name: '?????????', color: 'magenta' },
    2: { name: '??????' },
    7: { name: '?????????', color: 'green' },
}

export const CURRENT_MAP_VERSION = 0 as const;

//Map
type MapCityCompact = [number, number, number, number, number, number];
type MapNationCompact = [number, string, string, number];
export type MapResult = {
    result: true,
    version?: typeof CURRENT_MAP_VERSION,
    startYear: number,
    year: number,
    month: number,
    cityList: MapCityCompact[],
    nationList: MapNationCompact[],
    spyList: Record<number, number>,
    shownByGeneralList: number[],
    myCity?: number,
    myNation?: number,
}

export type CachedMapResult = MapResult & {
    theme?: string,
    history?: string[],
}

export type SimpleNationObj = {
    capital: number,
    cities: string[],
    color: string,
    gennum: number,
    level: number,
    name: string,
    nation: number,
    power: number,
    type: GameIActionKey
}