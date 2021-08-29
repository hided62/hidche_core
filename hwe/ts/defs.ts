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