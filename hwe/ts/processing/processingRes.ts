import type { ItemTypeKey } from "@/defs";
import { combineArray } from "@/util/combineArray";
import { type Ref, ref, watch } from "vue";

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

export type procGeneralKey = 'no' | 'name' | 'nationID' | 'officerLevel' | 'npc' | 'gold' | 'rice' | 'leadership' | 'strength' | 'intel' | 'cityID' | 'crew' | 'train' | 'atmos' | 'troopID';

export type procGeneralRawItem = procGeneralItem[procGeneralKey][];

export type procTroopItem = {
    troop_leader: number,
    nation: number,
    name: string
};
export type procTroopList = Record<number, procTroopItem>;

export type procGeneralRawItemList = procGeneralRawItem[];

export function convertGeneralList(keys: procGeneralKey[], rawList: procGeneralRawItemList): procGeneralList {
    return combineArray(rawList, keys) as procGeneralList;
}


export type procNationItem = {
    id: number,
    name: string,
    color: string,
    power: number,
    scoutMsg?: string,
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


export type procArmTypeItem = {
    armType: number,
    armName: string,
    values: procCrewTypeItem[],
}

export type procCrewTypeItem = {
    id: number,
    reqTech: number,
    reqYear: number,
    notAvailable?: boolean,
    baseRice: number,
    baseCost: number,
    name: string,
    attack: number,
    defence: number,
    speed: number,
    avoid: number,
    img: string,
    info: string[],
}

export type procItemType = {
    id: string,
    name: string,
    reqSecu: number,
    cost: number,
    info: string,//<br>
    isBuyable: boolean,
}

export type procItemList = Record<ItemTypeKey, {
    typeName: string,
    values: procItemType[],
}>


//XXX: vuex 쓰기 전까지...
export const searchableProcessingMode = 'sam.processing.searchable';
const searchable = ref((localStorage.getItem(searchableProcessingMode) ?? "0") != "0");
watch(searchable, (val) => {
    localStorage.setItem(searchableProcessingMode, val ? "1" : "0");
});

export function getProcSearchable():Ref<boolean>{
    return searchable;
}