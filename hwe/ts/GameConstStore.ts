import { SammoAPI } from './SammoAPI';
import type { GetConstResponse } from './defs/API/Global';
import type { CityID, CrewTypeID, GameCityDefault, GameConstType, GameUnitType, GameIActionCategory, GameIActionKey, GameIActionInfo } from './defs/GameObj';

export class GameConstStore {
    public readonly gameConst: GameConstType;
    public readonly gameUnitConst: Record<CrewTypeID, GameUnitType>;
    public readonly cityConst: Record<CityID, GameCityDefault>;
    public readonly cityConstMap: {
        region: Record<number | string, string | number>;
        level: Record<number | string, string | number>; //defs.CityLevelText
    };
    public readonly iActionInfo: Record<
        GameIActionCategory,
        Record<
            GameIActionKey,
            GameIActionInfo
        >
    >;
    public readonly iActionKeyMap: Record<string, GameIActionCategory>;

    constructor(response: GetConstResponse) {
        const data = response.data;
        this.gameConst = Object.freeze(data.gameConst);
        this.gameUnitConst = Object.freeze(data.gameUnitConst);
        this.cityConst = Object.freeze(data.cityConst);
        this.cityConstMap = Object.freeze(data.cityConstMap);
        this.iActionInfo = Object.freeze(data.iActionInfo);
        this.iActionKeyMap = Object.freeze(data.iActionKeyMap);
    }
}

let gameConstStore: GameConstStore | undefined = undefined;

export async function getGameConstStore(): Promise<GameConstStore> {
    //TODO: LocalStorage Cache 조합도 생각해보기.
    if (gameConstStore !== undefined) {
        return gameConstStore;
    }
    try {
        const result = await SammoAPI.Global.GetConst();
        gameConstStore = new GameConstStore(result);
    }
    catch (e: unknown) {
        console.error(`FATAL!: GameConst를 가져오지 못함: ${e}`);
        throw e;
    }
    return gameConstStore;
}