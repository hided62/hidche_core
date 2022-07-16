import type { ValuesOf, TurnObj, NationStaticItem } from "@/defs";
import type { GameObjClassKey } from "@/defs/GameObj";
import type { ValidResponse } from "@/SammoAPI";

export type SetBlockWarResponse = ValidResponse & {
  availableCnt: number;
};

export type GeneralListItemP0 = {
  no: number,
  name: string,
  nation: number,
  npc: number,
  injury: number,
  leadership: number,
  strength: number,
  intel: number,
  explevel: number,
  dedlevel: number,
  gold: number,
  rice: number,
  killturn: number,
  picture: string,
  imgsvr: 0 | 1,
  age: number,
  specialDomestic: GameObjClassKey,
  specialWar: GameObjClassKey,
  personal: GameObjClassKey,
  belong: number,
  connect: number,

  officerLevel: number, //권한에따라 태수,군사,시종 노출 여부가 다름
  officerLevelText: string,
  lbonus: number,
  ownerName: string | null, //NPC 출력용에 따라 결과가 다름
  honorText: string,
  dedLevelText: string,
  bill: number,
  reservedCommand: TurnObj[] | null,

  autorun_limit: number,
}

export type GeneralListItemP1 = {
  con: number,
  specage: number,
  specage2: number,
  leadership_exp: number,
  strength_exp: number,
  intel_exp: number,

  dex1: number,
  dex2: number,
  dex3: number,
  dex4: number,
  dex5: number,

  city: number,
  experience: number,
  dedication: number,
  officer_level: number,
  officer_city: number,
  defence_train: number,
  troop: number,
  crewtype: GameObjClassKey,
  crew: number,
  train: number,
  atmos: number,
  turntime: string,
  recent_war: string,
  horse: GameObjClassKey,
  weapon: GameObjClassKey,
  book: GameObjClassKey,
  item: GameObjClassKey,

  warnum: number,
  killnum: number,
  deathnum: number,
  killcrew: number,
  deathcrew: number,
  firenum: number,
} & GeneralListItemP0;

export type GeneralListItemP2 = GeneralListItemP1;

export type RawGeneralListItem = GeneralListItemP0 | GeneralListItemP1 | GeneralListItemP2;

export type GeneralListItem =
  (GeneralListItemP0 & { st0: true, st1: false, st2: false, permission: 0 }) |
  (GeneralListItemP1 & { st0: true, st1: true, st2: false, permission: 1 }) |
  (GeneralListItemP2 & { st0: true, st1: true, st2: true, permission: 2 | 3 | 4 });

type ResponseEnv = {
  year: number,
  month: number,
  turntime: string,
  turnterm: number,
  killturn: number,
  autorun_user?: {
    limit_minutes: number,
    options: Record<string, number>,
  }
}

export type RawGeneralListP0 = ValidResponse & {
  permission: 0,
  column: (keyof GeneralListItemP0)[],
  list: ValuesOf<GeneralListItemP0>[][],
  troops?: null,
  env: ResponseEnv,
}

export type RawGeneralListP1 = ValidResponse & {
  permission: 1,
  column: (keyof GeneralListItemP1)[],
  list: ValuesOf<GeneralListItemP1>[][],
  troops?: null,
  env: ResponseEnv,
}

export type RawGeneralListP2 = ValidResponse & {
  permission: 2 | 3 | 4,
  column: (keyof GeneralListItemP2)[],
  list: ValuesOf<GeneralListItemP2>[][],
  troops: [number, string][],
  env: ResponseEnv,
}

export type GeneralListResponse = RawGeneralListP0 | RawGeneralListP1 | RawGeneralListP2;

export type NationItem = NationStaticItem & {
  gold: number;
  rice: number;
  bill: number;
  rate: number;
  secretlimit: number;
  chief_set: number;
  scout: number;
  war: number;
  strategic_cmd_limit: number;
  surlimit: number;
  tech: number;
}

export type LiteNationInfoResponse = ValidResponse & {
  nation: NationStaticItem;
}

export type NationInfoResponse = (ValidResponse & {
  nation: NationStaticItem;
}) | (ValidResponse &{
  nation: NationItem;
  impossibleStrategicCommandLists: [string, number][];
  troops: Record<number, string>;
})