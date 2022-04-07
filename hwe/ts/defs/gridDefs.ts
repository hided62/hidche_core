import type { ColumnState } from "ag-grid-community";

export type GridDisplaySetting = {
  column: ColumnState[];
  columnGroup: {
    groupId: string;
    open: boolean;
  }[];
};
export const defaultDisplaySetting: Record<"war" | "normal", GridDisplaySetting> = {
  war: {
    column: [
      {
        colId: "icon",
        width: 80,
        hide: true,
        sort: null,
      },
      {
        colId: "name",
        width: 126,
        hide: false,
        sort: null,
      },
      {
        colId: "stat_1",
        width: 88,
        hide: false,
        sort: null,
      },
      {
        colId: "troop",
        width: 90,
        hide: false,
        sort: null,
      },
      {
        colId: "leadership",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "strength",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "intel",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "officerLevel",
        width: 70,
        hide: true,
        sort: null,
      },
      {
        colId: "expDedLv_1",
        width: 60,
        hide: true,
        sort: null,
      },
      {
        colId: "explevel",
        width: 60,
        hide: true,
        sort: null,
      },
      {
        colId: "dedlevel",
        width: 70,
        hide: true,
        sort: null,
      },
      {
        colId: "goldRice_1",
        width: 80,
        hide: false,
        sort: null,
      },
      {
        colId: "gold",
        width: 70,
        hide: false,
        sort: null,
      },
      {
        colId: "rice",
        width: 70,
        hide: false,
        sort: null,
      },
      {
        colId: "city",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "crewtypeAndCrew_1",
        width: 80,
        hide: false,
        sort: null,
      },
      {
        colId: "crewtype",
        width: 80,
        hide: false,
        sort: null,
      },
      {
        colId: "crew",
        width: 70,
        hide: false,
        sort: null,
      },
      {
        colId: "trainAtmos_1",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "train",
        width: 70,
        hide: false,
        sort: null,
      },
      {
        colId: "atmos",
        width: 70,
        hide: false,
        sort: null,
      },
      {
        colId: "defence_train",
        width: 50,
        hide: false,
        sort: null,
      },
      {
        colId: "specials_1",
        width: 80,
        hide: true,
        sort: null,
      },
      {
        colId: "personal",
        width: 60,
        hide: true,
        sort: null,
      },
      {
        colId: "specialDomestic",
        width: 60,
        hide: true,
        sort: null,
      },
      {
        colId: "specialWar",
        width: 60,
        hide: true,
        sort: null,
      },
      {
        colId: "reservedCommandShort_1",
        width: 70,
        hide: false,
        sort: null,
      },
      {
        colId: "reservedCommand",
        width: 120,
        hide: false,
        sort: null,
      },
      {
        colId: "turntime",
        width: 60,
        hide: false,
        sort: "asc",
        sortIndex: 0,
      },
      {
        colId: "recent_war",
        width: 60,
        hide: true,
        sort: null,
      },
      {
        colId: "years_1",
        width: 60,
        hide: true,
        sort: null,
      },
      {
        colId: "age",
        width: 60,
        hide: true,
        sort: null,
      },
      {
        colId: "belong",
        width: 60,
        hide: true,
        sort: null,
      },
      {
        colId: "killturnAndConnect_1",
        width: 70,
        hide: false,
        sort: null,
      },
      {
        colId: "killturn",
        width: 70,
        hide: false,
        sort: null,
      },
      {
        colId: "connect",
        width: 70,
        hide: true,
        sort: null,
      },
      {
        colId: "warResults_1",
        hide: true,
        sort: null,
      },
      {
        colId: "warnum",
        hide: true,
        sort: null,
      },
      {
        colId: "killnum",
        hide: true,
        sort: null,
      },
      {
        colId: "killcrew",
        hide: true,
        sort: null,
      },
    ],
    columnGroup: [
      {
        groupId: "0",
        open: false,
      },
      {
        groupId: "1",
        open: false,
      },
      {
        groupId: "stat",
        open: false,
      },
      {
        groupId: "2",
        open: false,
      },
      {
        groupId: "expDedLv",
        open: false,
      },
      {
        groupId: "goldRice",
        open: true,
      },
      {
        groupId: "3",
        open: false,
      },
      {
        groupId: "4",
        open: false,
      },
      {
        groupId: "crewtypeAndCrew",
        open: false,
      },
      {
        groupId: "trainAtmos",
        open: false,
      },
      {
        groupId: "specials",
        open: false,
      },
      {
        groupId: "reservedCommandShort",
        open: true,
      },
      {
        groupId: "5",
        open: false,
      },
      {
        groupId: "6",
        open: false,
      },
      {
        groupId: "years",
        open: false,
      },
      {
        groupId: "killturnAndConnect",
        open: true,
      },
      {
        groupId: "warResults",
        open: false
      },
    ],
  },
  normal: {
    column: [
      {
        colId: "icon",
        width: 80,
        hide: false,
        pinned: "left",
        sort: null,
      },
      {
        colId: "name",
        width: 126,
        hide: false,
        pinned: "left",
        sort: null,
      },
      {
        colId: "officerLevel",
        width: 70,
        hide: false,
        sort: null,
      },
      {
        colId: "expDedLv_1",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "dedlevel",
        width: 70,
        hide: false,
        sort: null,
      },
      {
        colId: "explevel",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "stat_1",
        width: 88,
        hide: false,
        sort: null,
      },
      {
        colId: "leadership",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "strength",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "intel",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "troop",
        width: 90,
        hide: true,
        sort: null,
      },
      {
        colId: "goldRice_1",
        width: 80,
        hide: false,
        sort: null,
      },
      {
        colId: "gold",
        width: 70,
        hide: false,
        sort: null,
      },
      {
        colId: "rice",
        width: 70,
        hide: false,
        sort: null,
      },
      {
        colId: "city",
        width: 60,
        hide: true,
        sort: null,
      },
      {
        colId: "crewtypeAndCrew_1",
        width: 80,
        hide: true,
        sort: null,
      },
      {
        colId: "crewtype",
        width: 80,
        hide: true,
        sort: null,
      },
      {
        colId: "crew",
        width: 70,
        hide: true,
        sort: null,
      },
      {
        colId: "trainAtmos_1",
        width: 60,
        hide: true,
        sort: null,
      },
      {
        colId: "train",
        width: 70,
        hide: true,
        sort: null,
      },
      {
        colId: "atmos",
        width: 70,
        hide: true,
        sort: null,
      },
      {
        colId: "defence_train",
        width: 50,
        hide: true,
        sort: null,
      },
      {
        colId: "specials_1",
        width: 80,
        hide: false,
        sort: null,
      },
      {
        colId: "personal",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "specialDomestic",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "specialWar",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "reservedCommandShort_1",
        width: 70,
        hide: true,
        sort: null,
      },
      {
        colId: "reservedCommand",
        width: 120,
        hide: true,
        sort: null,
      },
      {
        colId: "turntime",
        width: 60,
        hide: true,
        sort: null,
      },
      {
        colId: "recent_war",
        width: 60,
        hide: true,
        sort: null,
      },
      {
        colId: "years_1",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "age",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "belong",
        width: 60,
        hide: false,
        sort: null,
      },
      {
        colId: "killturnAndConnect_1",
        width: 70,
        hide: false,
        sort: null,
      },
      {
        colId: "killturn",
        width: 70,
        hide: true,
        sort: null,
      },
      {
        colId: "connect",
        width: 70,
        hide: false,
        sort: "desc",
        sortIndex: 0,
      },
      {
        colId: "warResults_1",
        hide: true,
        sort: null,
      },
      {
        colId: "warnum",
        hide: true,
        sort: null,
      },
      {
        colId: "killnum",
        hide: true,
        sort: null,
      },
      {
        colId: "killcrew",
        hide: true,
        sort: null,
      }
    ],
    columnGroup: [
      {
        groupId: "0",
        open: false
      },
      {
        groupId: "1",
        open: false
      },
      {
        groupId: "stat",
        open: true
      },
      {
        groupId: "2",
        open: false
      },
      {
        groupId: "expDedLv",
        open: true
      },
      {
        groupId: "goldRice",
        open: true
      },
      {
        groupId: "3",
        open: false
      },
      {
        groupId: "4",
        open: false
      },
      {
        groupId: "crewtypeAndCrew",
        open: false
      },
      {
        groupId: "trainAtmos",
        open: false
      },
      {
        groupId: "specials",
        open: false
      },
      {
        groupId: "reservedCommandShort",
        open: true
      },
      {
        groupId: "5",
        open: false
      },
      {
        groupId: "6",
        open: false,
      },
      {
        groupId: "years",
        open: false
      },
      {
        groupId: "killturnAndConnect",
        open: true
      },
      {
        groupId: "warResults",
        open: false
      },
    ],
  },
};