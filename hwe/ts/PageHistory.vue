<template>
  <BContainer v-if="asyncReady" id="container" :toast="{ root: true }" class="bg0 pageHistory">
    <TopBackBar title="연감" type="close">
      <div>&nbsp;</div>
      <!-- HACK: variant에 정상적인 값을 넣어야 해서...-->
      <BDropdown class="optionMenu" right :variant="('sammo-base2' as 'primary')">
        <template #button-content><i class="bi bi-gear-fill"></i>&nbsp;설정</template>
        <BDropdownItem @click="isNationRankingBottom = !isNationRankingBottom"
          >국가 순서 위치 변경(모바일 전용)</BDropdownItem
        >
      </BDropdown>
    </TopBackBar>
    <div class="center row mx-0 s-border-tb">
      <div class="col-md-1 col-2 year-selector text-end align-self-center">연월 선택:</div>
      <BButton class="col-md-1 col-2" @click="queryYearMonth = unwrap(queryYearMonth) - 1">◀ 이전달</BButton>
      <div class="col-md-3 col-5 d-grid">
        <BFormSelect v-model="queryYearMonth" :options="generateYearMonthList()" />
      </div>
      <BButton class="col-md-1 col-2" @click="queryYearMonth = unwrap(queryYearMonth) + 1">다음달 ▶</BButton>
    </div>
    <div v-if="history" id="map_holder" :class="['row', 'gx-0', isNationRankingBottom ? 'isNationRankingBottom' : '']">
      <div class="map_position">
        <MapViewer
          :server-nick="serverNick"
          :serverID="queryServerID"
          :map-name="mapName"
          :map-data="history.map"
          :is-detail-map="true"
          :city-position="cityPosition"
          :format-city-info="formatCityInfoText"
          :image-path="imagePath"
          :disallow-click="true"
        />
      </div>
      <div class="nation_position"><SimpleNationList :nations="history.nations" /></div>
      <div class="world_history col-12">
        <div class="bg1 center s-border-tb"><b>중원 정세</b></div>
        <div class="content">
          <template v-for="(item, idx) in history.global_history" :key="idx">
            <!-- eslint-disable-next-line vue/no-v-html -->
            <div v-html="formatLog(item)" />
          </template>
        </div>
      </div>
      <div class="general_public_record col-12">
        <div class="bg1 center s-border-tb"><b>장수 동향</b></div>
        <div class="content">
          <template v-for="(item, idx) in history.global_action" :key="idx">
            <!-- eslint-disable-next-line vue/no-v-html -->
            <div v-html="formatLog(item)" />
          </template>
        </div>
      </div>
    </div>
    <BottomBar type="close"></BottomBar>
  </BContainer>
</template>

<script lang="ts">
declare const staticValues: {
  firstYearMonth: number;
  lastYearMonth: number;
  currentYearMonth: number;
  serverNick: string;
  serverID: string;
  mapName: string;
};
declare const query: {
  yearMonth: number | null;
  serverID: string;
};

declare const getCityPosition: () => CityPositionMap;
declare const formatCityInfo: (city: MapCityParsedRaw) => MapCityParsed;
</script>
<script lang="ts" setup>
import { onMounted, provide, ref, watch } from "vue";
import { BContainer, BButton, BFormSelect, BDropdown, BDropdownItem } from "bootstrap-vue-3";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import type { HistoryObj } from "./defs/API/Global";
import { SammoAPI } from "./SammoAPI";
import { joinYearMonth } from "./util/joinYearMonth";
import { parseYearMonth } from "./util/parseYearMonth";
import { formatLog } from "./utilGame/formatLog";
import SimpleNationList from "./components/SimpleNationList.vue";
import MapViewer, { type CityPositionMap, type MapCityParsedRaw, type MapCityParsed } from "./components/MapViewer.vue";
import { getGameConstStore, type GameConstStore } from "./GameConstStore";
import { unwrap } from "@/util/unwrap";
import { useLocalStorage } from "@vueuse/core";

const queryYearMonth = ref<number>();
const queryServerID = query.serverID;
const serverNick = staticValues.serverNick;

const lastYearMonth = ref(staticValues.lastYearMonth);
const firstYearMonth = ref(staticValues.firstYearMonth);
const currentYearMonth = ref(staticValues.currentYearMonth);

const asyncReady = ref<boolean>(false);
const gameConstStore = ref<GameConstStore>();
provide("gameConstStore", gameConstStore);
const storeP = getGameConstStore().then((store) => {
  gameConstStore.value = store;
});

void Promise.all([storeP]).then(() => {
  asyncReady.value = true;
});

const isNationRankingBottom = useLocalStorage(`${serverNick}_isNationRankingBottom`, false);

const mapName = staticValues.mapName;
const cityPosition = getCityPosition();
const formatCityInfoText = formatCityInfo;
const imagePath = window.pathConfig.gameImage;

const history = ref<HistoryObj>();

function generateYearMonthList(): { text: string; value: number }[] {
  const result: { text: string; value: number }[] = [];
  let yearMonth = firstYearMonth.value;
  while (yearMonth <= lastYearMonth.value) {
    const [year, month] = parseYearMonth(yearMonth);
    const info: string[] = [];
    if (queryYearMonth.value === yearMonth) {
      info.push("선택");
    }
    result.push({ text: `${year}년 ${month}월 ${info.length > 0 ? `(${info.join(", ")})` : ""}`, value: yearMonth });
    yearMonth += 1;
  }
  const [year, month] = parseYearMonth(yearMonth);
  const info: string[] = [];
  if (queryYearMonth.value === yearMonth) {
    info.push("선택");
  }

  if (staticValues.serverID === query.serverID) {
    info.push("현재");
    result.push({ text: `${year}년 ${month}월 ${info.length > 0 ? `(${info.join(", ")})` : ""}`, value: yearMonth });
  }
  return result;
}

watch(queryYearMonth, async (yearMonth) => {
  if (yearMonth === undefined) {
    return;
  }

  if (yearMonth < firstYearMonth.value) {
    queryYearMonth.value = firstYearMonth.value;
    return;
  }

  if (staticValues.serverID === query.serverID) {
    if (yearMonth > lastYearMonth.value + 1) {
      queryYearMonth.value = lastYearMonth.value + 1;
      return;
    }
  } else {
    if (yearMonth > lastYearMonth.value) {
      queryYearMonth.value = lastYearMonth.value;
      return;
    }
  }

  if (yearMonth > lastYearMonth.value && staticValues.serverID === query.serverID) {
    try {
      const result = await SammoAPI.Global.GetCurrentHistory();
      history.value = result.data;
      console.log(result);
      const newLastYearMonth = joinYearMonth(result.data.year, result.data.month) - 1;
      if (newLastYearMonth > lastYearMonth.value) {
        lastYearMonth.value = newLastYearMonth;
        currentYearMonth.value = newLastYearMonth + 1;
      }
    } catch (e) {
      console.error(e);
      return;
    }
    return;
  }

  const [year, month] = parseYearMonth(yearMonth);
  try {
    const result = await SammoAPI.Global.GetHistory[queryServerID][year][month]();
    history.value = result.data;
    console.log(result);
  } catch (e) {
    console.error(e);
    return;
  }
});

onMounted(() => {
  queryYearMonth.value = (() => {
    if (query.yearMonth === null) {
      return staticValues.currentYearMonth;
    }
    if (query.yearMonth > staticValues.lastYearMonth + 1) {
      return staticValues.currentYearMonth;
    }
    if (query.yearMonth < staticValues.firstYearMonth) {
      return staticValues.currentYearMonth;
    }
    return query.yearMonth;
  })();
});
</script>

<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";

@include media-500px {
  .optionMenu::v-deep .dropdown-toggle {
    height: 32px;
  }
  .isNationRankingBottom {
    .nation_position {
      order: 4;
    }
  }
}
</style>
