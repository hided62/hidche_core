import type { CityID, CrewTypeID, GameCityDefault, GameConstType, GameUnitType, GameIActionKey, GameIActionCategory, GameIActionInfo } from "@/defs/GameObj";

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