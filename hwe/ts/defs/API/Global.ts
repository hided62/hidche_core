import type { CityID, CrewTypeID, GameCityDefault, GameConstType, GameUnitType, GameIActionKey, GameIActionCategory, GameIActionInfo } from "@/defs/GameObj";
import type { diplomacyState, MapResult, SimpleNationObj } from "@/defs";

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
        iActionInfo: Record<
            GameIActionCategory,
            Record<
                GameIActionKey,
                GameIActionInfo
            >
        >;
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
    };
}

export type HistoryObj = {
    server_id: string,
    year: number;
    month: number;
    map: MapResult,
    global_history: string[],
    global_action: string[],
    nations: {
        capital: number,
        cities: string[],
        color: string,
        gennum: number,
        level: number,
        name: string,
        nation: number,
        power: number,
        type: GameIActionKey
    }[],
}

export type GetHistoryResponse = {
    result: true;
    data: HistoryObj & {
        hash: string;
        //no: number,
    };
}

export type GetCurrentHistoryResponse = {
    result: true;
    data: HistoryObj;
}


export type GetDiplomacyResponse = {
    result: true;
    nations: SimpleNationObj[];
    conflict: [number, Record<number, number>][];
    diplomacyList: Record<number, Record<number, diplomacyState>>;
    myNationID: number;
}

export type GetRecentRecordResponse = {
    result: true;
    history: [number, string][];
    global: [number, string][];
    general: [number, string][];
    flushHistory: 1 | 0;
    flushGlobal: 1 | 0;
    flushGeneral: 1 | 0;
}