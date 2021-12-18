import { combineArray } from "@/util/combineArray";

export type procGeneralItem = {
    no: number,
    name: string,
    officerLevel: number,
    npc: number,
    gold: number,
    rice: number,
    leadership: number,
    strength: number,
    intel: number,
    cityID: number,
    crew: number,
    train: number,
    atmos: number,
    troopID: number,
}

export type procGeneralList = procGeneralItem[];

export type procGeneralKeyList = [
    'no', 'name', 'officerLevel', 'npc', 'gold', 'rice', 'leadership', 'strength', 'intel', 'cityID', 'crew', 'train', 'atmos', 'troopID'
];

export type procGeneralRawItem = [
    procGeneralItem[procGeneralKeyList[0]],
    procGeneralItem[procGeneralKeyList[1]],
    procGeneralItem[procGeneralKeyList[2]],
    procGeneralItem[procGeneralKeyList[3]],
    procGeneralItem[procGeneralKeyList[4]],
    procGeneralItem[procGeneralKeyList[5]],
    procGeneralItem[procGeneralKeyList[6]],
    procGeneralItem[procGeneralKeyList[7]],
    procGeneralItem[procGeneralKeyList[8]],
    procGeneralItem[procGeneralKeyList[9]],
    procGeneralItem[procGeneralKeyList[10]],
    procGeneralItem[procGeneralKeyList[11]],
    procGeneralItem[procGeneralKeyList[12]],
    procGeneralItem[procGeneralKeyList[13]],
];

export type procTroopItem = {
    troop_leader: number,
    nation: number,
    name: string
};
export type procTroopList = Record<number, procTroopItem>;

export type procGeneralRawItemList = procGeneralRawItem[];

export function convertGeneralList(keys: procGeneralKeyList, rawList: procGeneralRawItemList): procGeneralList{
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