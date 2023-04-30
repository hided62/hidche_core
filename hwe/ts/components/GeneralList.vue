<template>
  <Teleport v-if="toolbarID" :to="`#${toolbarID}`">
    <BButtonGroup class="d-flex general-list-toolbar">
      <BDropdown class="w-50" menuClass="view-mode-list" variant="primary" text="보기 모드">
        <BDropdownItem @click="setDisplaySetting([true, 'normal'], defaultDisplaySetting.normal)">기본</BDropdownItem>
        <BDropdownItem @click="setDisplaySetting([true, 'war'], defaultDisplaySetting.war)">전투</BDropdownItem>
        <BDropdownDivider />
        <BDropdownItem @click="storeDisplaySetting"><i class="bi bi-bookmark-plus-fill" />&nbsp;보관하기</BDropdownItem>
        <BDropdownDivider />
        <BDropdownItem
          v-for="[key, setting] of displaySettings.entries()"
          :key="key"
          @click="setDisplaySetting([false, key], setting)"
          ><div class="row gx-0">
            <div class="col-9 text-wrap">
              <span class="align-middle">{{ key }}</span>
            </div>
            <div class="col-3">
              <div class="d-grid"><BButton size="sm" @click="deleteDisplaySetting(key)">삭제</BButton></div>
            </div>
          </div></BDropdownItem
        >
      </BDropdown>
      <!-- eslint-disable-next-line vue/max-attributes-per-line -->
      <BDropdown class="w-50" variant="info" text="열 선택" menuClass="column-menu" right>
        <template v-for="[colID, col, depth] of getColumnList()" :key="[colID, depth]">
          <BDropdownItem v-if="col instanceof ProvidedColumnGroup" disabled>
            <span :style="{ marginLeft: depth ? `${12 * depth}px` : undefined }">
              {{ col.getColGroupDef()?.headerName }}</span
            ></BDropdownItem
          >
          <BDropdownItem v-else>
            <div :style="{ marginLeft: depth ? `${12 * depth}px` : undefined }" class="form-check" @click.stop="1">
              <input
                :id="`column-type-${colID}`"
                class="form-check-input"
                type="checkbox"
                :checked="col.isVisible()"
                @change.stop="toggleColumn(colID, col)"
              />
              <label
                class="form-check-label"
                :for="`column-type-${colID}`"
                :style="{
                  textDecoration: validColumns.has(colID) ? undefined : 'line-through',
                }"
              >
                {{ col.getColDef().headerName }}
              </label>
            </div></BDropdownItem
          >
        </template>
        <BDropdownDivider />
      </BDropdown>
    </BButtonGroup>
  </Teleport>
  <div
    class="component-general-list"
    :style="{
      height: props.height === 'fill' ? '100%' : props.height === 'static' ? undefined : `${props.height}px`,
    }"
  >
    <AgGridVue
      style="width: 100%; height: 100%"
      class="ag-theme-balham-dark"
      :getRowId="getRowId"
      :getRowHeight="getRowHeight"
      :columnDefs="columnDefs"
      :rowData="list"
      :defaultColDef="defaultColDef"
      :suppressColumnMoveAnimation="suppressColumnMoveAnimation"
      @grid-ready="onGridReady"
      @cell-clicked="onCellClicked"
    />
  </div>
</template>
<script lang="ts"></script>
<script lang="ts" setup>
import type { GeneralListItem, GeneralListItemP1, GeneralListItemP2, GeneralListResponse } from "@/defs/API/Nation";
import { getIconPath } from "@/util/getIconPath";
import { inject, ref, watch, type PropType, type Ref, type StyleValue } from "vue";
import { AgGridVue } from "ag-grid-vue3";
import {
  Column,
  CellClassParams,
  CellStyle,
  ColDef,
  ColGroupDef,
  ColumnApi,
  GetRowIdParams,
  GridApi,
  GridReadyEvent,
  CellClickedEvent,
  IRowNode,
  NumberFilter,
} from "ag-grid-community";
import { ProvidedColumnGroup } from "ag-grid-community";
import { getNPCColor } from "@/utilGame";
import type {
  ValueGetterParams,
  ValueFormatterFunc,
  ValueGetterFunc,
  ValueFormatterParams,
} from "ag-grid-community/dist/lib/entities/colDef";
import type { GameConstStore } from "@/GameConstStore";
import { unwrap } from "@/util/unwrap";
import SimpleTooltipCell from "@/gridCellRenderer/SimpleTooltipCell.vue";
import GridTooltipCell, { type GridCellInfo } from "@/gridCellRenderer/GridTooltipCell.vue";
import { formatConnectScore } from "@/utilGame/formatConnectScore";
import { convertSearch초성 } from "@/util/convertSearch초성";
import { isString } from "lodash-es";
import { formatDefenceTrain } from "@/utilGame/formatDefenceTrain";
import { BDropdownItem, BDropdownDivider, BButtonGroup, BDropdown, BButton } from "bootstrap-vue-next";
import { unwrap_err } from "@/util/unwrap_err";
import { RuntimeError } from "@/util/RuntimeError";
import { defaultDisplaySetting, type GridDisplaySetting } from "@/defs/gridDefs";
const props = defineProps({
  list: {
    type: Array as PropType<GeneralListItem[]>,
    required: true,
  },
  troops: {
    type: Object as PropType<Record<number, string>>,
    required: true,
  },
  height: {
    type: String as PropType<"static" | "fill" | number | `${number}px` | `${number}%`>,
    required: false,
    default: "static",
  },
  env: {
    type: Object as PropType<GeneralListResponse["env"]>,
    required: true,
  },
  toolbarID: {
    type: String,
    required: false,
    default: undefined,
  },
  role: {
    type: String,
    required: false,
    default: "generic",
  },
  availableGeneralClick: {
    type: Boolean,
    required: false,
    default: true,
  },
});

const emit = defineEmits<{
  (e: "generalClick", generalID: number): void;
}>();

const suppressColumnMoveAnimation = ref(true);
const gameConstStore = unwrap(inject<Ref<GameConstStore>>("gameConstStore"));

const validColumns = ref(new Set<string>());

watch(
  () => props.list,
  (newValue) => {
    const newValidColumns = new Set<string>(["icon"]);
    if (newValue.length > 0) {
      for (const key of Object.keys(newValue[0])) {
        newValidColumns.add(key);
      }
      validColumns.value = newValidColumns;
    }
    setTimeout(() => {
      gridApi.value?.redrawRows();
    }, 0);
  }
);
watch(
  () => props.height,
  (val) => {
    if (val === "static") {
      gridApi.value?.setDomLayout("autoHeight");
    } else {
      gridApi.value?.setDomLayout("normal");
    }
  }
);
const generalByID = ref(new Map<number, GeneralListItem>());
function refineGeneralList(list: GeneralListItem[]) {
  const map = new Map<number, GeneralListItem>();
  for (const general of list) {
    map.set(general.no, general);
  }
  generalByID.value = map;
}
refineGeneralList(props.list);
watch(() => props.list, refineGeneralList);
const gridApi = ref<GridApi>();
const columnApi = ref<ColumnApi>();
const rowHeight = ref(68);
function getRowId(params: GetRowIdParams): string {
  const genID = (params.data as GeneralListItem).no;
  return `${genID}`;
}

function setDisplaySetting(settingKey: SettingKeyType, setting: GridDisplaySetting) {
  if (!columnApi.value) {
    console.error("nyc?");
    return;
  }
  columnApi.value.applyColumnState({ state: setting.column, applyOrder: true });
  columnApi.value.setColumnGroupState(setting.columnGroup);
  currentSetting.value = settingKey;
}

const displaySettings = ref(new Map<string, GridDisplaySetting>());
const displaySettingVersion = 1; //추가되는 걸로 버전 올리지 말고, 사용할 수 없게 될때만 올리기
const displaySettingsKey = "GeneralListDisplaySetting";

function getLastUsedSettingsKey() {
  const lastUsedSettingsKey = "LastUsedSettingsKey";
  return `${lastUsedSettingsKey}_${props.role}`;
}

function loadDisplaySetting() {
  const rawSettings = localStorage.getItem(displaySettingsKey);
  if (!rawSettings) {
    return;
  }
  const settings: { version: number; settings: [string, GridDisplaySetting][] } = JSON.parse(rawSettings);
  if (settings.version != displaySettingVersion) {
    localStorage.removeItem(displaySettingsKey);
    return;
  }

  displaySettings.value = new Map(settings.settings);
}
loadDisplaySetting();

type SettingKeyType = [true, keyof typeof defaultDisplaySetting] | [false, string];
const currentSetting = ref<SettingKeyType>([true, "normal"]);
function loadLastUsedSettings() {
  const rawLastSettingKey = localStorage.getItem(getLastUsedSettingsKey());
  if (!rawLastSettingKey) {
    return;
  }
  const settingKey: SettingKeyType = JSON.parse(rawLastSettingKey);
  const [isDefault, settingKeyName] = settingKey;
  if (isDefault) {
    if (!(settingKeyName in defaultDisplaySetting)) {
      console.error(`${settingKeyName}은 이제 기본 지원 타입이 아닙니다.`);
      return;
    }
  } else {
    if (!displaySettings.value.has(settingKeyName)) {
      console.error(`${settingKeyName}는 저장되어있지 않습니다.`);
      return;
    }
  }

  currentSetting.value = settingKey;
  return settingKey;
}
watch(currentSetting, (newTypeKey) => {
  localStorage.setItem(getLastUsedSettingsKey(), JSON.stringify(newTypeKey));
});

watch(
  displaySettings,
  (newSettings) => {
    const settings = Array.from(newSettings.entries());
    localStorage.setItem(
      displaySettingsKey,
      JSON.stringify({
        version: displaySettingVersion,
        settings,
      })
    );
    console.log("저장!", Array.from(newSettings.keys()));
  },
  { deep: true }
);

function deleteDisplaySetting(key: string) {
  if (!confirm(`${key} 설정을 지울까요?`)) {
    return;
  }
  displaySettings.value.delete(key);
}

function storeDisplaySetting() {
  if (!columnApi.value) {
    console.error("nyc?");
    return;
  }

  const nickName = prompt("선택한 설정의 별명을 지어주세요", currentSetting.value[0] ? "" : currentSetting.value[1]);
  if (!nickName) {
    return;
  }

  if (displaySettings.value.has(nickName)) {
    if (!confirm("이미 있는 이름입니다. 덮어쓸까요?")) {
      return;
    }
  }

  const setting: GridDisplaySetting = {
    column: columnApi.value.getColumnState(),
    columnGroup: columnApi.value.getColumnGroupState(),
  };

  displaySettings.value.set(nickName, setting);
  currentSetting.value = [false, nickName];
}

function onGridReady(params: GridReadyEvent) {
  gridApi.value = params.api;
  columnApi.value = params.columnApi;
  if (props.height === "static") {
    params.api.setDomLayout("autoHeight");
  } else {
    params.api.setDomLayout("normal");
  }
  loadLastUsedSettings();
  if (currentSetting.value[0]) {
    setDisplaySetting(currentSetting.value, defaultDisplaySetting[currentSetting.value[1]]);
  } else {
    setDisplaySetting(currentSetting.value, unwrap(displaySettings.value.get(currentSetting.value[1])));
  }

  setTimeout(() => {
    suppressColumnMoveAnimation.value = false;
  }, 1);
}

function onCellClicked(event: CellClickedEvent) {
  const colID = event.column.getColId();
  if (colID === "icon" || colID === "name") {
    const generalItem = event.data as GeneralListItem;
    emit("generalClick", generalItem.no);
  }
}

function getRowHeight(): number {
  return rowHeight.value;
}
type headerType =
  | keyof Omit<GeneralListItemP2, "no" | "imgsvr" | "picture" | "lbonus" | "permission" | "st0" | "st1" | "st2">
  | "stat"
  | "icon"
  | "goldRice"
  | "expDedLv"
  | "crewtypeAndCrew"
  | "trainAtmos"
  | "specials"
  | "reservedCommandShort"
  | "killturnAndConnect"
  | "years"
  | "warResults";

type GenValueParams<T = unknown> = ValueFormatterParams<GeneralListItem, T>;
type GenValueGetterParams = ValueGetterParams<GeneralListItem>;

type GenRowNode = IRowNode<GeneralListItem>;

interface GenColDef extends ColDef<GeneralListItem> {
  colId: headerType;
  field?: headerType;
  headerName: string;
  valueFormatter?: string | ValueFormatterFunc;
  filterValueGetter?: string | ValueGetterFunc;
  valueGetter?: string | ValueGetterFunc;
}
interface GenColGroupDef extends ColGroupDef {
  headerName: string;
  children: GenColDef[]; //1단만 할꺼다!
  groupId: headerType;
}

function getColumnList(): [headerType, ProvidedColumnGroup | Column, number?][] {
  const result: [headerType, ProvidedColumnGroup | Column, number?][] = [];
  if (!columnApi.value) {
    return result;
  }
  for (const [rawColKey, rawColDef] of Object.entries(columnRawDefs.value)) {
    if (rawColKey === "name") {
      continue;
    }
    if (!("children" in rawColDef)) {
      const col = unwrap_err(columnApi.value.getColumn(rawColDef.colId), RuntimeError, `no col: ${rawColDef.colId}`);
      result.push([rawColDef.colId, col]);
      continue;
    }
    const colGroup = unwrap_err(
      columnApi.value.getProvidedColumnGroup(rawColDef.groupId),
      RuntimeError,
      `no colGroup: ${rawColDef.groupId}`
    );
    result.push([rawColDef.groupId, colGroup]);
    for (const subColDef of rawColDef.children) {
      const subColId = subColDef.colId;
      if (rawColDef.groupId == subColId) {
        continue;
      }
      const col = unwrap_err(columnApi.value.getColumn(subColId), RuntimeError, `no subCol: ${subColDef.colId}`);
      result.push([subColId, col, 1]);
    }
  }
  return result;
}
function naiveCheClassNameFilter(value: string): string {
  if (!value) {
    return "-";
  }
  const text = value.split("_").pop() ?? "None";
  if (text === "None") {
    return "-";
  }
  return text;
}
function numberFormatter(unit?: string) {
  if (unit) {
    return (value: ValueFormatterParams<GeneralListItem, number>): string => {
      const valueText = value.value.toLocaleString();
      return `${valueText} ${unit}`;
    };
  }
  return (value: ValueFormatterParams<number>): string => {
    return value.value.toLocaleString();
  };
}
function extractTroopInfo(value: GeneralListItem | undefined): [string, GeneralListItemP1] | undefined {
  if (value === undefined) {
    return undefined;
  }
  if (!value.st1) {
    return undefined;
  }
  const troopID = value.troop;
  if (!(troopID in props.troops)) {
    return undefined;
  }
  const troopName = props.troops[troopID];
  const troopLeader = generalByID.value.get(troopID) as GeneralListItemP1 | undefined;
  if (troopLeader === undefined) {
    return undefined;
  }
  return [troopName, troopLeader];
}
function toggleColumn(colID: headerType, col: Column) {
  const newState = !col.isVisible();
  const target: string[] = [colID];
  const parent = col.getParent();
  if (newState) {
    for (const child of (parent.getChildren() ?? []) as Column[]) {
      if (parent.getGroupId() == child.getColDef().colId) {
        target.push(child.getColId());
        break;
      }
    }
  } else {
    let stillVisible = false;
    let header: string | null = null;
    for (const child of (parent.getChildren() ?? []) as Column[]) {
      if (child.getColId() == colID) {
        continue;
      }
      if (parent.getGroupId() == child.getColDef().colId) {
        header = child.getColId();
        continue;
      }
      stillVisible = true;
      break;
    }
    if (!stillVisible && header) {
      target.push(header);
    }
  }
  columnApi.value?.setColumnsVisible(target, newState);
}
const defaultCellClass = ["cell-middle"];
const centerCellClass = [...defaultCellClass, "cell-center"];
const rightAlignClass = [...defaultCellClass, "cell-right"];
const sortableNumber: Omit<GenColDef, "colId" | "headerName"> = {
  sortable: true,
  comparator: (a, b, _a, _b, _desc) => a - b,
  sortingOrder: ["desc", "asc", null],
  filter: NumberFilter,
  cellClass: rightAlignClass,
};
const defaultColDef = ref<ColDef>({
  resizable: true,
  headerClass: "default-cell-header",
  cellClass: centerCellClass,
  floatingFilter: true,
  width: 80,
});
const columnRawDefs = ref<Partial<Record<headerType, GenColDef | GenColGroupDef>>>({
  icon: {
    colId: "icon",
    headerName: "아이콘",
    width: 64 + 16,
    suppressSizeToFit: true,
    resizable: false,
    cellRenderer: (obj: GenValueParams) => {
      const { data: gen } = obj;
      if (gen === undefined) {
        return "";
      }
      return `<img src="${getIconPath(gen.imgsvr, gen.picture)}" width="64">`;
    },
    pinned: "left",
    cellClass: [props.availableGeneralClick ? "clickable-cell" : "", ...defaultCellClass],
    lockPosition: true,
  },
  name: {
    headerName: "장수명",
    colId: "name",
    field: "name",
    pinned: "left",
    sortable: true,
    width: 120,
    sortingOrder: ["asc", "desc", null],
    lockPosition: true,
    cellStyle: (val: CellClassParams<GeneralListItem>) => {
      const gen = unwrap(val.data);
      const style: StyleValue = {
        color: getNPCColor(gen.npc),
      };
      return style as CellStyle;
    },
    comparator: (_lhs, _rhs, { data: lhs }: GenRowNode, { data: rhs }: GenRowNode, _desc) => {
      if (lhs === undefined) {
        return 1;
      }
      if (rhs === undefined) {
        return -1;
      }
      const npcDiff = lhs.npc - rhs.npc;
      if (npcDiff != 0) {
        return npcDiff;
      }
      return lhs.name.localeCompare(rhs.name);
    },

    filterValueGetter: (data: GenValueGetterParams) => convertSearch초성(unwrap(data.data).name).join(""),
    cellClass: [props.availableGeneralClick ? "clickable-cell" : "", ...defaultCellClass],
    filter: true,
    hide: false,
    lockVisible: true,
  },
  //npc: { headerName: "NPC", colId: "npc", field: "npc" },
  stat: {
    groupId: "stat",
    openByDefault: false,
    headerName: "능력치",
    children: [
      {
        colId: "stat",
        headerName: "통|무|지",
        width: 88,
        cellRenderer: (obj: GenValueParams) => {
          const gen = unwrap(obj.data);
          return `${gen.leadership}|${gen.strength}|${gen.intel}`;
        },
        columnGroupShow: "closed",
      },
      {
        colId: "leadership",
        headerName: "통솔",
        field: "leadership",
        ...sortableNumber,
        columnGroupShow: "open",
        width: 60,
        type: "numericColumn",
      },
      {
        colId: "strength",
        headerName: "무력",
        field: "strength",
        ...sortableNumber,
        columnGroupShow: "open",
        width: 60,
      },
      {
        colId: "intel",
        headerName: "지력",
        field: "intel",
        ...sortableNumber,
        columnGroupShow: "open",
        width: 60,
      },
    ],
  },

  officerLevel: {
    headerName: "관직",
    colId: "officerLevel",
    field: "officerLevelText",
    sortable: true,
    comparator: (a, b, c, d, _desc) => unwrap(c.data).officerLevel - unwrap(d.data).officerLevel,
    cellRenderer: ({ data }: GenValueParams) => {
      if (data === undefined) {
        return "";
      }
      if (data.officerLevel >= 5) {
        return `<span style="color:cyan;">${data.officerLevelText}</span>`;
      }
      if (data.st1 && 2 <= data.officerLevel && data.officerLevel <= 4) {
        const cityName = gameConstStore.value.cityConst[data.officer_city].name;
        return `${cityName}<br>${data.officerLevelText}`;
      }
      return data.officerLevelText;
    },
    filterValueGetter: ({ data }) => {
      if (data.st1 && 2 <= data.officerLevel && data.officerLevel <= 4) {
        const cityName = gameConstStore.value.cityConst[data.officer_city].name;
        return convertSearch초성(`${cityName} ${data.officerLevelText}`);
      }
      return convertSearch초성(data.officerLevelText);
    },
    filter: true,
    cellClass: centerCellClass,
    width: 70,
  },
  expDedLv: {
    headerName: "명성/계급",
    groupId: "expDedLv",
    width: 70,
    children: [
      {
        colId: "expDedLv",
        headerName: "",
        columnGroupShow: "closed",
        width: 60,
        cellRenderer: ({ data }: GenValueParams) => {
          if (data === undefined) {
            return "";
          }
          return `Lv ${data.explevel}<br>${data.dedLevelText}`;
        },
      },
      {
        colId: "explevel",
        headerName: "명성",
        field: "explevel",
        width: 60,
        cellRenderer: ({ data }: GenValueParams) => {
          if (data === undefined) {
            return "";
          }
          return `Lv ${data.explevel}<br>(${data.honorText})`;
        },
        ...sortableNumber,
        cellClass: centerCellClass,
        columnGroupShow: "open",
      },
      {
        colId: "dedlevel",
        headerName: "계급",
        field: "dedLevelText",
        width: 70,
        cellRenderer: ({ data }: GenValueParams) => {
          if (data === undefined) {
            return "";
          }
          return `${data.dedLevelText}<br>(${data.bill.toLocaleString()})`;
        },
        sortable: true,
        comparator: (a, b, _a, _b, _desc) => {
          return (_a.data?.dedlevel??0) - (_b.data?.dedlevel??0);
        },
        sortingOrder: ["desc", "asc", null],
        filter: true,
        cellClass: centerCellClass,
        columnGroupShow: "open",
      },
    ],
  },
  goldRice: {
    headerName: "자금",
    groupId: "goldRice",
    children: [
      {
        colId: "goldRice",
        headerName: "금/쌀",
        cellRenderer: ({ data }: GenValueParams) => {
          if (data === undefined) {
            return "";
          }
          return `${data.gold.toLocaleString()} 금<br>${data.rice.toLocaleString()} 쌀`;
        },
        width: 80,
        cellClass: rightAlignClass,
        columnGroupShow: "closed",
        sortable: true,
        sortingOrder: ["desc", "asc", null],
        comparator(_a, _b, { data: lhs }: GenRowNode, { data: rhs }: GenRowNode, _desc) {
          if (lhs === undefined) {
            return -1;
          }
          if (rhs === undefined) {
            return 1;
          }
          const lhsAmount = lhs.gold + lhs.rice;
          const rhsAmount = rhs.gold + rhs.rice;
          return lhsAmount - rhsAmount;
        },
      },
      {
        colId: "gold",
        headerName: "금",
        field: "gold",
        ...sortableNumber,
        valueFormatter: numberFormatter("금"),
        width: 70,
        columnGroupShow: "open",
      },
      {
        colId: "rice",
        headerName: "쌀",
        field: "rice",
        ...sortableNumber,
        valueFormatter: numberFormatter("쌀"),
        width: 70,
        columnGroupShow: "open",
      },
    ],
  },
  city: {
    colId: "city",
    headerName: "도시",
    field: "city",
    valueGetter: ({ data }) => {
      if (!data.st1) {
        return "?";
      }
      return gameConstStore.value.cityConst[data.city].name;
    },
    filter: true,
    sortable: true,
    width: 60,
    filterValueGetter: ({ data }) => {
      if (!data.st1) {
        return "";
      }
      return convertSearch초성(gameConstStore.value.cityConst[data.city].name);
    },
  },
  troop: {
    colId: "troop",
    headerName: "부대",
    field: "troop",
    valueGetter: ({ data }: GenValueGetterParams) => {
      if (data === undefined || !data.st1) {
        return "?";
      }
      const troopInfo = extractTroopInfo(data);
      if (troopInfo === undefined) {
        return "-";
      }
      const [troopName, troopLeader] = troopInfo;
      const cityName = gameConstStore.value.cityConst[troopLeader.city].name;
      return [troopName, cityName];
    },
    cellRenderer: ({ value }: { value: [string, string] | string }) => {
      if (isString(value)) {
        return value;
      }
      const [troopName, cityName] = value;
      return `${troopName}<br>[${cityName}]`;
    },
    width: 90,
    sortable: true,
    comparator: (valX, valB, { data: lhs }: GenRowNode, { data: rhs }: GenRowNode, _desc) => {
      const troopInfoLhs = extractTroopInfo(lhs);
      const troopInfoRhs = extractTroopInfo(rhs);
      console.log(troopInfoLhs, troopInfoRhs);
      if (troopInfoLhs === troopInfoRhs) {
        return 0;
      }
      if (troopInfoLhs === undefined) {
        return 1;
      }
      if (troopInfoRhs === undefined) {
        return -1;
      }
      return troopInfoLhs[0].localeCompare(troopInfoRhs[0]);
    },
    filter: true,
    filterValueGetter: ({ data }) => {
      const troopInfo = extractTroopInfo(data);
      if (troopInfo === undefined) {
        return "-";
      }
      const [troopName, troopLeader] = troopInfo;
      const cityName = gameConstStore.value.cityConst[troopLeader.city].name;
      return convertSearch초성(`${troopName}$${cityName}`);
    },
  },
  crewtypeAndCrew: {
    groupId: "crewtypeAndCrew",
    headerName: "보유 병력",
    children: [
      {
        colId: "crewtypeAndCrew",
        headerName: "병종",
        cellRenderer: GridTooltipCell,
        cellRendererParams: {
          cells: ((): GridCellInfo[][] => {
            return [
              [{ target: "crewtype", iActionMap: gameConstStore.value.iActionInfo.crewtype }],
              [{ target: "crew", converter: (value) => [`${value.crew.toLocaleString()}명`, undefined] }],
            ];
          })(),
        },
        columnGroupShow: "closed",
      },
      {
        colId: "crewtype",
        headerName: "병종",
        field: "crewtype",
        cellRenderer: SimpleTooltipCell,
        cellRendererParams: {
          iActionMap: gameConstStore.value.iActionInfo.crewtype,
        },
        sortable: true,
        columnGroupShow: "open",
        filter: true,
        filterValueGetter: ({ data }) => {
          if (!data.st1) {
            return "?";
          }
          const name = gameConstStore.value.iActionInfo.crewtype[data.crewtype].name;
          return convertSearch초성(name);
        },
      },
      {
        colId: "crew",
        headerName: "병력",
        field: "crew",
        ...sortableNumber,
        valueFormatter: numberFormatter("명"),
        width: 70,
        columnGroupShow: "open",
      },
    ],
  },
  trainAtmos: {
    groupId: "trainAtmos",
    headerName: "훈/사",
    children: [
      {
        colId: "trainAtmos",
        headerName: "훈/사",
        width: 60,
        cellRenderer: ({ data }: GenValueParams) => {
          if (data === undefined || !data.st1) {
            return "?";
          }
          return `${data.train}<br>${data.atmos}`;
        },
        columnGroupShow: "closed",
      },
      {
        colId: "train",
        headerName: "훈련",
        field: "train",
        ...sortableNumber,
        valueFormatter: numberFormatter(),
        width: 70,
        columnGroupShow: "open",
      },
      {
        colId: "atmos",
        headerName: "사기",
        field: "atmos",
        ...sortableNumber,
        valueFormatter: numberFormatter(),
        width: 70,
        columnGroupShow: "open",
      },
      {
        colId: "defence_train",
        headerName: "수비",
        field: "defence_train",
        sortable: true,
        sortingOrder: ["desc", "asc", null],
        valueFormatter: (value: GenValueParams<number>) => formatDefenceTrain(value.value),
        width: 50,
      },
    ],
  },
  specials: {
    groupId: "specials",
    headerName: "특성",
    children: [
      {
        colId: "specials",
        headerName: "요약",
        cellRenderer: GridTooltipCell,
        cellRendererParams: {
          cells: ((): GridCellInfo[][] => {
            return [
              [{ target: "personal", iActionMap: gameConstStore.value.iActionInfo.personality }],
              [
                { target: "specialDomestic", iActionMap: gameConstStore.value.iActionInfo.specialDomestic },
                { target: "specialWar", iActionMap: gameConstStore.value.iActionInfo.specialWar },
              ],
            ];
          })(),
        },
        width: 80,
        columnGroupShow: "closed",
      },
      {
        colId: "personal",
        headerName: "성격",
        field: "personal",
        cellRenderer: SimpleTooltipCell,
        cellRendererParams: {
          iActionMap: gameConstStore.value.iActionInfo.personality,
        },
        width: 60,
        sortable: true,
        filter: true,
        columnGroupShow: "open",
        filterValueGetter: ({ data }) => {
          const name = gameConstStore.value.iActionInfo.personality[data.personal].name;
          return convertSearch초성(name);
        },
      },
      {
        colId: "specialDomestic",
        headerName: "내특",
        field: "specialDomestic",
        cellRenderer: SimpleTooltipCell,
        cellRendererParams: {
          iActionMap: gameConstStore.value.iActionInfo.specialDomestic,
        },
        width: 60,
        sortable: true,
        filter: true,
        columnGroupShow: "open",
        filterValueGetter: ({ data }) => {
          const name = gameConstStore.value.iActionInfo.specialDomestic[data.specialDomestic].name;
          return convertSearch초성(name);
        },
      },
      {
        colId: "specialWar",
        headerName: "전특",
        field: "specialWar",
        cellRenderer: SimpleTooltipCell,
        cellRendererParams: {
          iActionMap: gameConstStore.value.iActionInfo.specialWar,
        },
        width: 60,
        sortable: true,
        filter: true,
        columnGroupShow: "open",
        filterValueGetter: ({ data }) => {
          const name = gameConstStore.value.iActionInfo.specialWar[data.specialWar].name;
          return convertSearch초성(name);
        },
      },
    ],
  },
  reservedCommandShort: {
    groupId: "reservedCommandShort",
    headerName: "명령",
    children: [
      {
        colId: "reservedCommandShort",
        headerName: "단축",
        width: 70,
        cellRenderer: ({ data }: GenValueParams) => {
          if (data === undefined) {
            return "?";
          }
          if (data.npc >= 2) {
            return "NPC 장수";
          }
          if (!data.reservedCommand) {
            return "-";
          }
          const commandList = data.reservedCommand;
          if (!commandList) {
            return "???";
          }
          return commandList
            .map(({ action }) => {
              if (action !== "휴식" || data.npc >= 2) {
                return naiveCheClassNameFilter(action);
              }
              const limitMinutes = props.env.autorun_user?.limit_minutes ?? 0;
              if (!limitMinutes) {
                return naiveCheClassNameFilter(action);
              }
              if (data.killturn + limitMinutes > props.env.killturn) {
                return "자율행동";
              }
              return naiveCheClassNameFilter(action);
            })
            .join("<br>");
        },
        cellStyle: {
          lineHeight: "1em",
          fontSize: "0.85em",
        },
        columnGroupShow: "closed",
      },
      {
        colId: "reservedCommand",
        headerName: "전체",
        width: 120,
        cellRenderer: ({ data }: GenValueParams) => {
          if (data === undefined) {
            return "?";
          }
          if (data.npc >= 2) {
            return "NPC 장수";
          }
          const commandList = data.reservedCommand;
          if (!commandList) {
            return "???";
          }
          return commandList
            .map(({ action, brief }) => {
              if (action !== "휴식" || data.npc >= 2) {
                return brief;
              }
              const limitMinutes = props.env.autorun_user?.limit_minutes ?? 0;
              if (!limitMinutes) {
                return brief;
              }
              if (data.killturn + limitMinutes > props.env.killturn) {
                return "자율 행동";
              }
              return brief;
            })
            .join("<br>");
        },
        cellStyle: {
          lineHeight: "1em",
          fontSize: "0.85em",
        },
        columnGroupShow: "open",
      },
    ],
  },
  turntime: {
    colId: "turntime",
    headerName: "턴",
    field: "turntime",
    width: 60,
    valueFormatter: ({ value, data }) => {
      if (!data.st1) {
        return "?";
      }
      const turntime = value as string;
      return turntime.substring(14, 19);
    },
    sortable: true,
    cellClass: centerCellClass,
  },
  recent_war: {
    colId: "recent_war",
    headerName: "최근전투",
    field: "recent_war",
    width: 60,
    valueFormatter: ({ value, data }: GenValueParams<string>) => {
      if (data === undefined || !data.st1) {
        return "?";
      }
      const turntime = value as string;
      return turntime.substring(14, 19);
    },
    sortable: true,
    cellClass: centerCellClass,
  },
  years: {
    groupId: "years",
    headerName: "연도",
    children: [
      {
        colId: "years",
        headerName: "요약",
        width: 60,
        cellRenderer: ({ data }: GenValueParams) => {
          if (data === undefined) {
            return "?";
          }
          return `${data.age}세<br>${data.belong}년`;
        },
        cellClass: centerCellClass,
        columnGroupShow: "closed",
      },
      {
        colId: "age",
        headerName: "연령",
        field: "age",
        ...sortableNumber,
        valueFormatter: (v: GenValueParams<number>) => `${v.value}세`,
        width: 60,
        cellClass: centerCellClass,
        columnGroupShow: "open",
      },
      {
        colId: "belong",
        headerName: "사관",
        field: "belong",
        ...sortableNumber,
        valueFormatter: (v: GenValueParams<number>) => `${v.value}년`,
        width: 60,
        cellClass: centerCellClass,
        columnGroupShow: "open",
      },
    ],
  },
  killturnAndConnect: {
    groupId: "killturnAndConnect",
    headerName: "기타",
    children: [
      {
        colId: "killturnAndConnect",
        headerName: "삭/벌",
        cellRenderer: ({ data }: GenValueParams) => {
          if (data === undefined) {
            return "?";
          }
          return `${data.killturn.toLocaleString()}턴<br>${data.connect.toLocaleString()}점`;
        },
        cellClass: rightAlignClass,
        columnGroupShow: "closed",
        width: 70,
      },
      {
        colId: "killturn",
        headerName: "삭턴",
        field: "killturn",
        cellRenderer: ({ data }: GenValueParams) => {
          if (data === undefined) {
            return "?";
          }
          return `${data.killturn.toLocaleString()}턴`;
        },
        ...sortableNumber,
        width: 70,
        columnGroupShow: "open",
      },
      {
        colId: "connect",
        headerName: "벌점",
        field: "connect",
        cellRenderer: ({ data }: GenValueParams) => {
          if (data === undefined) {
            return "?";
          }
          return `${data.connect.toLocaleString()}점<br>(${formatConnectScore(data.connect)})`;
        },
        ...sortableNumber,
        width: 70,
        columnGroupShow: "open",
      },
    ],
  },
  warResults: {
    groupId: "warResults",
    headerName: "전과",
    children: [
      {
        colId: "warResults",
        headerName: "요약",
        cellRenderer: ({ data }: GenValueParams) => {
          if (data === undefined || !data.st1) {
            return "?";
          }
          const killRatePercent = Math.round((data.killcrew / Math.max(1, data.deathcrew)) * 100);
          return `${data.warnum.toLocaleString()}전 ${data.killnum.toLocaleString()}승<br>살상: ${killRatePercent}%`;
        },
        cellClass: centerCellClass,
        columnGroupShow: "closed",
        width: 90,
      },
      {
        colId: "warnum",
        headerName: "전투",
        field: "warnum",
        ...sortableNumber,
        valueFormatter: numberFormatter("전"),
        columnGroupShow: "open",
        width: 60,
      },
      {
        colId: "killnum",
        headerName: "승리",
        field: "killnum",
        ...sortableNumber,
        valueFormatter: numberFormatter("승"),
        columnGroupShow: "open",
        width: 60,
      },
      {
        colId: "killcrew",
        headerName: "살상률",
        field: "killcrew",
        ...sortableNumber,
        valueGetter: ({ data }) => {
          if (!data.st1) {
            return "?";
          }
          const killRatePercent = Math.round((data.killcrew / Math.max(1, data.deathcrew)) * 100);
          return killRatePercent;
        },
        valueFormatter: numberFormatter("%"),
        columnGroupShow: "open",
        width: 60,
      },
    ],
  },
});
const columnDefs = ref([...Object.values(columnRawDefs.value)]);
watch(columnRawDefs, (val) => {
  columnDefs.value = [...Object.values(val)];
  gridApi.value?.refreshCells();
});
</script>
<style scoped lang="scss">
.g-tr {
  border-bottom: solid gray 1px;
}
.g-thead-tr {
  position: sticky;
  top: 0px;
  z-index: 5;
}

:deep(.view-mode-list) {
  width: 180px;
}
</style>
<style lang="scss">
.component-general-list {
  .clickable-cell:hover {
    text-decoration: underline;
    cursor: pointer;
  }
  .ag-root-wrapper .cell-middle {
    display: flex;
    align-items: center;
  }
  .ag-root-wrapper {
    font-family: "Pretendard", "Apple SD Gothic Neo", "Noto Sans KR", "Malgun Gothic";
    font-size: 14px;
    overflow: auto;
  }
  .ag-root {
    overflow: auto;
  }
  .ag-header {
    position: sticky;
    top: 0;
    z-index: 10;
  }
  .cell-center {
    justify-content: space-around;
    text-align: center;
  }
  .cell-right {
    justify-content: flex-end;
    text-align: right;
  }
  .cell-sp .col {
    min-width: 30px;
  }
  .ag-header-cell,
  .ag-header-group-cell,
  .ag-cell {
    padding-left: 4px;
    padding-right: 4px;
  }
  .ag-header-cell-label,
  .ag-header-group-cell-label {
    justify-content: center;
  }
  .ag-ltr .ag-floating-filter-button {
    margin-left: 2px;
  }
  .ag-rtl .ag-floating-filter-button {
    margin-right: 2px;
  }
}
.general-list-toolbar {
  .column-menu {
    column-count: 3;
  }
}
</style>
