import type { GameConstStore } from "@/GameConstStore";
import { unwrap } from "@/util/unwrap";
import { pick as josaPick } from "@/util/JosaUtil";
import type { TurnObj } from "@/defs";

export function postFilterNationCommandGen<T extends TurnObj>(troopList: Record<number, string>, gameConst: GameConstStore){
  return function(turnObj: T): T{
    if(turnObj.action != 'che_발령'){
      return turnObj;
    }
    const destGeneralID = unwrap(turnObj.arg.destGeneralID);
    if(!(destGeneralID in troopList)){
      return turnObj;
    }

    const troopName = troopList[destGeneralID];
    const destCityID = unwrap(turnObj.arg.destCityID);
    const destCityName = gameConst.cityConst[destCityID].name;
    const josaRo = josaPick(destCityName, "로");
    const brief = `《${troopName}》【${destCityName}】${josaRo} 발령`;
    const tooltip = `《${troopName}》${turnObj.brief}`;

    return {
      ...turnObj,
      brief,
      tooltip,
    }
  }

}