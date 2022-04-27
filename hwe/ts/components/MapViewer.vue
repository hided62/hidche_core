<template>
  <div
    v-if="(mapData.version ?? 0) == CURRENT_MAP_VERSION"
    :id="uuid"
    :class="[
      'world_map',
      `map_theme_${mapTheme}`,
      drawableMap ? '' : 'draw_required',
      props.isDetailMap ? 'map_detail' : 'map_basic',
      hideMapCityName ? 'hide_cityname' : '',
      isFullWidth ? 'full_width_map' : 'small_width_map',
      getMapSeasonClassName(),
    ]"
  >
    <div
      v-my-tooltip.hover.top="{
        class: 'map_title_tooltiptext',
      }"
      class="map_title"
      :title="getTitleTooltip()"
    >
      <span class="map_title_text" :style="{ color: getTitleColor() }"
        >{{ mapData?.year }}年 {{ mapData?.month }}月</span
      >
      <span class="tooltiptext" />
    </div>
    <div ref="map_area" class="map_body" @click="clickOutside">
      <div class="map_bglayer1" />
      <div class="map_bglayer2" />
      <div class="map_bgroad" />
      <div class="map_button_stack">
        <button
          type="button"
          :class="['btn btn-primary map_toggle_cityname btn-sm btn-minimum', hideMapCityName ? 'active' : '']"
          data-bs-toggle="button"
          :aria-pressed="hideMapCityName"
          autocomplete="off"
          @click="hideMapCityName = !hideMapCityName"
        >
          도시명 표기</button
        ><br />
        <button
          :style="{
            display: deviceType != 'mouseOnly' ? 'block' : 'none',
          }"
          type="button"
          :class="['btn btn-secondary map_toggle_single_tap btn-sm btn-minimum', toggleSingleTap ? 'active' : '']"
          data-bs-toggle="button"
          :aria-pressed="toggleSingleTap"
          autocomplete="off"
          @click="toggleSingleTap = !toggleSingleTap"
        >
          두번 탭 해 도시 이동
        </button>
      </div>
      <template v-if="drawableMap === undefined"><!--로딩중?--></template>
      <template v-else-if="props.isDetailMap">
        <MapCityDetail
          v-for="city of drawableMap.cityList"
          :key="city.id"
          :city="city"
          :image-path="imagePath"
          :is-my-city="city.id === drawableMap.myCity"
          :isFullWidth="isFullWidth"
          :href="props.genHref?.call(city, city.id)"
          @click="cityClick(city, $event)"
          @mouseenter="mouseenter(city, $event)"
          @mouseleave="mouseleave(city, $event)"
        />
      </template>
      <template v-else
        ><MapCityBasic
          v-for="city of drawableMap.cityList"
          :key="city.id"
          :city="city"
          :is-my-city="city.id === drawableMap.myCity"
          :isFullWidth="isFullWidth"
          :href="props.genHref?.call(city, city.id)"
          @click="cityClick(city, $event)"
          @mouseenter="mouseenter(city, $event)"
          @mouseleave="mouseleave(city, $event)"
      /></template>
    </div>
    <div
      ref="tooltipDom"
      class="city_tooltip"
      :style="{
        display: isOutside || !activatedCity ? 'none' : 'block',
        position: 'absolute',
        left: `${(() => {
          if (cursorX + tooltipWidth + 10 > (isFullWidth ? 700 : 500)) {
            return cursorX - tooltipWidth - 5;
          }
          return cursorX + 10;
        })()}px`,
        top: `${cursorY + 30}px`,
      }"
    >
      <div class="city_name">{{ activatedCity?.text }}</div>
      <div class="nation_name">{{ activatedCity?.nation }}</div>
    </div>
  </div>
  <div v-else class="world_map">
    <span class="map_title_text">
      맵 버전이 맞지 않습니다.<br />
      렌더러 버전: {{ CURRENT_MAP_VERSION }}<br />
      API 버전: {{ mapData.version ?? 0 }}
    </span>
  </div>
</template>

<script lang="ts">
export type MapCityParsedRaw = {
  id: number;
  level: number;
  state: number;
  nationID?: number;
  region: number;
  supply: boolean;
};

type MapCityParsedName = MapCityParsedRaw & {
  name: string;
  x: number;
  y: number;
};

type MapCityParsedNation = MapCityParsedName & {
  nationID?: number;
  nation?: string;
  color?: string;
  isCapital: boolean;
};

type MapCityParsedClickable = MapCityParsedNation & {
  clickable: number;
};

type MapCityParsedRegionLevelText = MapCityParsedClickable & {
  region_str: string;
  level_str: string;
  text: string;
};

export type MapCityParsed = MapCityParsedRegionLevelText;

type MapCityDrawable = {
  cityList: MapCityParsed[];
  myCity?: number;
};

type MapNationParsed = {
  id: number;
  name: string;
  color: string;
  capital: number;
};

export type CityPositionMap = {
  [cityID: number]: [string, number, number];
};
</script>
<script lang="ts" setup>
import "@/../scss/map.scss";
import { type PropType, toRef, inject, type Ref, ref, watch, type ComponentPublicInstance } from "vue";
import { v4 as uuidv4 } from "uuid";
import { CURRENT_MAP_VERSION, type MapResult } from "@/defs";
import { joinYearMonth } from "@/util/joinYearMonth";
import { parseYearMonth } from "@/util/parseYearMonth";
import vMyTooltip from "@/directives/vMyTooltip";
import type { GameConstStore } from "@/GameConstStore";
import { unwrap_err } from "@/util/unwrap_err";
import { getMaxRelativeTechLevel, TECH_LEVEL_YEAR_GAP } from "@/utilGame/techLevel";
import { deviceType } from "detect-it";
import MapCityBasic from "./MapCityBasic.vue";
import MapCityDetail from "./MapCityDetail.vue";
import { convertDictById } from "@/common_legacy";
import { useElementSize, useMouse, useMouseInElement } from "@vueuse/core";
import { hideMapCityName, toggleSingleTap } from "@/state/mapViewer";
import { is1000pxMode } from "@/state/is1000pxMode";
const uuid = uuidv4();
const gameConstStore = unwrap_err(
  inject<Ref<GameConstStore>>("gameConstStore"),
  Error,
  "gameConstStore가 주입되지 않았습니다."
);

const tooltipDom = ref<ComponentPublicInstance<HTMLDivElement>>();
const map_area = ref<ComponentPublicInstance<HTMLDivElement>>();
const { elementX: cursorX, elementY: cursorY, isOutside } = useMouseInElement(map_area);

const tooltipWidth = ref(0);
const { width: tooltipCurrWidth } = useElementSize(tooltipDom);
watch(tooltipCurrWidth, (newWidth)=>{
  if(newWidth == 0) return;
  tooltipWidth.value = newWidth;
}, {immediate: true});

const { sourceType: cursorType } = useMouse();
const emit = defineEmits<{
  (event: "city-click", city: MapCityParsed, e: MouseEvent | TouchEvent): void;
  (event: "parsed", drawable: MapCityDrawable): void;
  (event: 'update:modelValue', value: MapCityParsed): void;
}>();

const isFullWidth = ref(true);

function setWidthMode([widthMode, is1000pxMode]: ["auto" | "full" | "small" | undefined, boolean]): void {
  if (widthMode == "full") {
    isFullWidth.value = true;
  }
  if (widthMode == "small") {
    isFullWidth.value = false;
  }
  isFullWidth.value = is1000pxMode;
}
watch([() => props.width, is1000pxMode], setWidthMode, { immediate: true });

const props = defineProps({
  width: {
    type: String as PropType<"full" | "small" | "auto" | undefined>,
    default: undefined,
    required: false,
  },
  imagePath: {
    type: String,
    required: true,
  },
  mapName: {
    type: String,
    required: true,
  },
  isDetailMap: { type: Boolean, default: undefined, required: false },
  disallowClick: { type: Boolean, default: undefined, required: false },
  genHref: {
    type: Function as PropType<(cityID: number) => string>,
    default: undefined,
    required: false,
  },

  cityPosition: {
    type: Object as PropType<CityPositionMap>,
    required: true,
  },
  formatCityInfo: {
    type: Function as PropType<(city: MapCityParsed) => MapCityParsed>,
    required: true,
  },

  mapData: {
    type: Object as PropType<MapResult>,
    required: true,
  },

  modelValue: {
    type: Object as PropType<MapCityParsed>,
    default: undefined,
    required: false,
  }
});

const mapData = toRef(props, "mapData");

const mapTheme = toRef(props, "mapName");
function getTitleColor(): string | undefined {
  const { startYear, year } = mapData.value;

  if (year < startYear + 1) {
    return "magenta";
  }
  if (year < startYear + 2) {
    return "orange";
  }
  if (year < startYear + 3) {
    return "yellow";
  }
}

const drawableMap = ref<MapCityDrawable>(convertCityObjs(props.mapData));
const activatedCity = ref<MapCityParsed>();

function getBeginGameLimitTooltip(): string | undefined {
  const { startYear, year, month } = mapData.value;
  if (year > startYear + 3) return undefined;

  const [remainYear, remainMonth] = parseYearMonth(joinYearMonth(startYear + 3, 0) - joinYearMonth(year, month));

  return `초반제한 기간 : ${remainYear}년${remainMonth > 0 ? ` ${remainMonth}개월` : ""} (${startYear + 3}년)`;
}

function getTitleTooltip(): string {
  const result: string[] = [];
  const beginLimit = getBeginGameLimitTooltip();
  if (beginLimit) {
    result.push(beginLimit);
  }

  const { startYear, year } = mapData.value;

  const maxTechLevel = gameConstStore.value.gameConst.maxTechLevel;
  const currentTechLimit = getMaxRelativeTechLevel(startYear, year, maxTechLevel);

  if (currentTechLimit == maxTechLevel) {
    result.push(`기술등급 제한 : ${currentTechLimit}등급 (최종)`);
  } else {
    const nextTechLimitYear = currentTechLimit * TECH_LEVEL_YEAR_GAP + startYear;
    result.push(`기술등급 제한 : ${currentTechLimit}등급 (${nextTechLimitYear}년 해제)`);
  }

  return result.join("<br>");
}

function getMapSeasonClassName(): string {
  const { month } = mapData.value;

  if (month <= 3) {
    return "map_spring";
  }
  if (month <= 6) {
    return "map_summer";
  }
  if (month <= 9) {
    return "map_fall";
  }
  return "map_winter";
}

function convertCityObjs(obj: MapResult): MapCityDrawable {
  //원본 Obj는 굉장히 간소하게 온다, Object 형태로 변환해서 사용한다.

  function toCityObj([id, level, state, nationID, region, supply]: MapResult["cityList"][0]): MapCityParsedRaw {
    return {
      id: id,
      level: level,
      state: state,
      nationID: nationID > 0 ? nationID : undefined,
      region: region,
      supply: supply != 0,
    };
  }

  function toNationObj([id, name, color, capital]: MapResult["nationList"][0]): MapNationParsed {
    return {
      id,
      name,
      color,
      capital,
    };
  }

  const nationList = convertDictById(obj.nationList.map(toNationObj)); //array of object -> dict

  const spyList = obj.spyList;
  const shownByGeneralList = new Set(obj.shownByGeneralList);

  const myCity = obj.myCity;
  const myNation = obj.myNation;

  function mergePositionInfo(city: MapCityParsedRaw): MapCityParsedName {
    const id = city.id;
    if (!(id in props.cityPosition)) {
      throw TypeError(`알수 없는 cityID: ${id}`);
    }
    const [name, x, y] = props.cityPosition[id];

    return {
      ...city,
      name,
      x,
      y,
    };
  }

  function mergeNationInfo(city: MapCityParsedName): MapCityParsedNation {
    //nationID 값으로 isCapital, color, nation을 통합

    const nationID = city.nationID;
    if (nationID === undefined || !(nationID in nationList)) {
      return {
        ...city,
        isCapital: false,
      };
    }

    const nationObj = nationList[nationID];
    return {
      ...city,
      nation: nationObj.name,
      color: nationObj.color,
      isCapital: nationObj.capital == city.id,
    };
  }

  function mergeClickable(city: MapCityParsedNation): MapCityParsedClickable {
    //clickable = (defaultCity << 4 ) | (remainSpy << 3) | (ourCity << 2) | (shownByGeneral << 1)
    const id = city.id;
    const nationID = city.nationID;

    if (props.disallowClick) {
      return { ...city, clickable: 0 };
    }

    let clickable = 16;
    if (id in spyList) {
      clickable |= spyList[id] << 3;
    }
    if (myNation !== null && nationID == myNation) {
      clickable |= 4;
    }
    if (shownByGeneralList.has(id)) {
      clickable |= 2;
    }
    if (myCity !== null && id == myCity) {
      clickable |= 2;
    }

    return {
      ...city,
      clickable,
    };
  }

  const cityList = obj.cityList
    .map(toCityObj)
    .map(mergePositionInfo)
    .map(mergeNationInfo)
    .map(mergeClickable)
    .map(window.formatCityInfo);

  const result = {
    cityList: cityList,
    myCity: myCity,
  };
  emit("parsed", result);

  return result;
}
watch(
  () => props.mapData,
  (mapInfo) => {
    activatedCity.value = undefined;
    drawableMap.value = convertCityObjs(mapInfo);
  }
);

const touchState = ref(0);
function cityClick(city: MapCityParsed, $event: MouseEvent | TouchEvent): void {
  if (cursorType.value == "touch") {
    if (touchState.value == 1 && activatedCity.value?.id !== city.id) {
      touchState.value = 0;
      activatedCity.value = undefined;
    }
    if (touchState.value == 0) {
      touchState.value = 1;
      activatedCity.value = city;
      $event.preventDefault();
      if (!toggleSingleTap.value) {
        return;
      }
    }
  }
  emit("city-click", city, $event);
  emit("update:modelValue", city);
}

function clickOutside($event: MouseEvent): void {
  $event.preventDefault();
  $event.stopPropagation();
  if (touchState.value == 1) {
    touchState.value = 0;
    activatedCity.value = undefined;
  }
}

function mouseenter(city: MapCityParsed, $event: MouseEvent): void {
  if (cursorType.value == "mouse") {
    activatedCity.value = city;
    touchState.value = 0;
  }
}

function mouseleave(city: MapCityParsed, $event: MouseEvent): void {
  if (cursorType.value == "mouse") {
    activatedCity.value = undefined;
    touchState.value = 0;
  }
}
</script>
