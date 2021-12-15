export type InvalidResponse = {
    result: false;
    reason: string;
}

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

export type GeneralListResponse = {
    result: true,
    list: [
        number,
        string, 0 | 1,
        number, number, string,
        string, string, string,
        string, string | null,
        number,
        number, number, number, number,
        number,
        string, string, string,
        number, number
    ][],
    token: Record<number, number>,
}

export type NationLevel = 0 | 1 | 2 | 3 | 4 | 5 | 6 | 7;
export const NationLevelText: Record<NationLevel, string> = {
    0: '방랑군',
    1: '호족',
    2: '군벌',
    3: '주자사',
    4: '주목',
    5: '공',
    6: '왕',
    7: '황제',
}

export type CityLevel = 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8;
export const CityLevelText: Record<CityLevel, string> = {
    1: '수',
    2: '진',
    3: '관',
    4: '이',
    5: '소',
    6: '중',
    7: '대',
    8: '특'
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
    'NPC사망대비' |
    '귀환' |
    '금쌀구매' |
    '출병' |
    '긴급내정' |
    '전투준비' |
    '전방워프' |
    //'NPC증여'|
    'NPC헌납' |
    '징병' |
    '후방워프' |
    '전쟁내정' |
    '소집해제' |
    '일반내정' |
    '내정워프';

export type NPCChiefActions =
    '불가침제의' |
    '선전포고' |
    '천도' |
    '유저장긴급포상' |
    '부대전방발령' |
    '유저장구출발령' |
    '유저장후방발령' |
    '부대유저장후방발령' |
    '유저장전방발령' |
    '유저장포상' |
    //'유저장몰수'|
    '부대구출발령' |
    '부대후방발령' |
    'NPC긴급포상' |
    'NPC구출발령' |
    'NPC후방발령' |
    'NPC포상' |
    'NPC전방발령' |
    '유저장내정발령' |
    'NPC내정발령' |
    'NPC몰수';

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
}

export declare type Colors = 'primary' | 'secondary' | 'success' | 'danger' | 'warning' | 'info' | 'dark' | 'light';

export type IDItem<T> = {
    id: T;
};

export type ToastType = {
    title?: string,
    content?: string,
    type?: Colors,
    delay?: number,
}

export const keyScreenMode = 'sam.screenMode';
export type ScreenModeType = 'auto'|'500px'|'1000px';