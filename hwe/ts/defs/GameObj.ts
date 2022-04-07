import type { CityLevel, ItemTypeKey } from ".";

type CSSRGBColor = string;

export type GameObjClassKey = string;
export type GameIActionKey = GameObjClassKey;

type SpecialDomesticKey = GameIActionKey;
type SpecialWarKey = GameIActionKey;
type GameItemKey = GameIActionKey;
type MapTypeKey = string;
type UnitSetKey = string;
type RawHTMLString = string;
type NationTypeKey = GameIActionKey;
type GamePersonalityKey = GameIActionKey;
type GeneralCommandName = GameObjClassKey;
type ChiefCommandName = GameObjClassKey;

/** GameConst.php */
export type GameConstType = {
    title: string;
    banner: RawHTMLString;
    mapName: MapTypeKey;
    unitSet: UnitSetKey;
    develrate: number;
    upgradeLimit: number;
    dexLimit: number;
    defaultAtmosLow: number;
    defaultTrainLow: number;
    defaultAtmosHigh: number;
    defaultTrainHigh: number;
    maxAtmosByCommand: number;
    maxTrainByCommand: number;
    maxAtmosByWar: number;
    maxTrainByWar: number;
    trainDelta: number;
    atmosDelta: number;
    atmosSideEffectByTraining: number;
    trainSideEffectByAtmosTurn: number;
    sabotageDefaultProb: number;
    sabotageProbCoefByStat: number;
    sabotageDamageMin: number;
    sabotageDamageMax: number;
    basecolor: CSSRGBColor;
    basecolor2: CSSRGBColor;
    basecolor3: CSSRGBColor;
    basecolor4: CSSRGBColor;
    armperphase: number;
    basegold: number;
    baserice: number;
    minNationalGold: number;
    minNationalRice: number;
    exchangeFee: number;
    adultAge: number;
    minPushHallAge: number;
    maxDedLevel: number;
    maxTechLevel: number;
    maxBetrayCnt: number;

    basePopIncreaseAmount: number;
    expandCityPopIncreaseAmount: number;
    expandCityDevelIncreaseAmount: number;
    expandCityWallIncreaseAmount: number;
    expandCityDefaultCost: number;
    expandCityCostCoef: number;
    minAvailableRecruitPop: number;
    initialNationGenLimitForRandInit: number;
    initialNationGenLimit: number;

    defaultMaxGeneral: number;
    defaultMaxNation: number;
    defaultMaxGenius: number;
    defaultStartYear: number;

    joinRuinedNPCProp: number;

    defaultGold: number;
    defaultRice: number;

    coefAidAmount: number;

    maxResourceActionAmount: number;
    resourceActionAmountGuide: number[];

    generalMinimumGold: number;
    generalMinimumRice: number;

    maxTurn: number;
    maxChiefTurn: number;

    statGradeLevel: number;

    openingPartYear: number;
    joinActionLimit: number;

    bornMinStatBonus: number;
    bornMaxStatBonus: number;

    availableNationType: NationTypeKey[];
    neutralNationType: NationTypeKey;

    defaultSpecialDomestic: SpecialDomesticKey;
    availableSpecialDomestic: SpecialDomesticKey[];
    optionalSpecialDomestic: SpecialDomesticKey[];

    defaultSpecialWar: SpecialWarKey;
    availableSpecialWar: SpecialWarKey[];
    optionalSpecialWar: SpecialWarKey[];

    neutralPersonality: GamePersonalityKey;
    availablePersonality: GamePersonalityKey[];
    optionalPersonality: GamePersonalityKey[];

    maxUniqueItemLimit: [number, number][];

    maxAvailableWarSettingCnt: number;
    incAvailableWarSettingCnt: number;

    minMonthToAllowInheritItem: number;
    inheritBornSpecialPoint: number;
    inheritBornTurntimePoint: number;
    inheritBornCityPoint: number;
    inheritBornStatPoint: number;
    inheritItemUniqueMinPoint: number;
    inheritItemRandomPoint: number;
    inheritBuffPoints: number[];
    inheritSpecificSpecialPoint: number;
    inheritResetAttrPointBase: number[];

    allItems: Record<ItemTypeKey, Record<GameItemKey, number>>;

    availableGeneralCommand: Record<string, GeneralCommandName[]>;
    availableChiefCommand: Record<string, ChiefCommandName[]>;

    retirementYear: number;

    targetGeneralPool: GameObjClassKey;
    generalPoolAllowOption: string[];

    randGenFirstName: string[];
    randGenMiddleName: string[];
    randGenLastName: string[];

    npcBanMessageProb: number;
    npcSeizureMessageProb: number;
    npcMessageFreqByDay: number;

    /**
     * Scenario::getGameConf
     */

    defaultStatTotal: number;
    defaultStatMin: number;
    defaultStatMax: number;
    defaultStatNPCTotal: number;
    defaultStatNPCMax: number;
    defaultStatNPCMin: number;
    chiefStatMin: number;

};

export type CrewTypeID = number;
export type ArmTypeID = 0 | 1 | 2 | 3 | 4 | 5;

export type MapRegionID = number;
export type CityID = number;

export type GameUnitType = {
    id: CrewTypeID;
    armType: ArmTypeID;
    name: string;
    attack: number;
    defence: number;
    speed: number;
    avoid: number;
    magicCoef: number;
    cost: number;
    rice: number;
    reqTech: number;
    reqCities: CityID[] | null;
    reqRegions: MapRegionID[] | null;
    reqYear: number;
    attackCoef: Record<CrewTypeID | ArmTypeID, number> | null | [];
    defenceCoef: Record<CrewTypeID | ArmTypeID, number> | null | [];
    info: string | string[];
    initSkillTrigger: GameObjClassKey[] | null;
    phaseSkillTrigger: GameObjClassKey[] | null;
};

export type GameCityDefault = {
    id: CityID;
    name: string;
    level: CityLevel;
    population: number;
    agriculture: number;
    commerce: number;
    security: number;
    defence: number;
    wall: number;
    region: number;
    posX: number;
    posY: number;
    path: Record<CityID, string>;
};

export type GameIActionCategory = "nationType" | "specialDomestic" | "specialWar" | "personality" | "item" | "crewtype";

export type GameIActionInfo = {
    value: string;
    name: string;
    info?: string | null;
}