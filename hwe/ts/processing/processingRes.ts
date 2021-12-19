import { combineArray } from "@/util/combineArray";

export type procGeneralItem = {
    no: number,
    name: string,
    nationID?: number,
    officerLevel: number,
    npc: number,
    gold?: number,
    rice?: number,
    leadership: number,
    strength: number,
    intel: number,
    cityID?: number,
    crew?: number,
    train?: number,
    atmos?: number,
    troopID?: number,
}

export type procGeneralList = procGeneralItem[];

export type procGeneralKey = 'no'| 'name'| 'nationID' | 'officerLevel'| 'npc'| 'gold'| 'rice'| 'leadership'| 'strength'| 'intel'| 'cityID'| 'crew'| 'train'| 'atmos'| 'troopID';

export type procGeneralRawItem = procGeneralItem[procGeneralKey][];

export type procTroopItem = {
    troop_leader: number,
    nation: number,
    name: string
};
export type procTroopList = Record<number, procTroopItem>;

export type procGeneralRawItemList = procGeneralRawItem[];

export function convertGeneralList(keys: procGeneralKey[], rawList: procGeneralRawItemList): procGeneralList{
    return combineArray(rawList, keys) as procGeneralList;
}


export type procNationItem = {
    id: number,
    name: string,
    color: string,
    power: number,
    info?: string,
    notAvailable?: boolean,
};

export type procNationList = procNationItem[];

export type procNationTypeItem = {
    type: string,
    name: string,
    pros: string,
    cons: string,
}

export type procNationTypeList = Record<string, procNationTypeItem>;