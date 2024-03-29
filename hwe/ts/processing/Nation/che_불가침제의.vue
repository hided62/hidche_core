<template>
  <TopBackBar v-model:searchable="searchable" :title="commandName" type="chief" />
  <div v-if="asyncReady" class="bg0">
    <MapViewer
      v-if="map"
      v-model="selectedCityObj"
      :server-nick="serverNick"
      :serverID="serverID"
      :map-name="unwrap(gameConstStore?.gameConst.mapName)"
      :mapData="map"
      :isDetailMap="false"
      :cityPosition="cityPosition"
      :formatCityInfo="formatCityInfoText"
      :image-path="imagePath"
    />

    <div>
      타국에게 불가침을 제의합니다.<br />
      제의할 국가를 목록에서 선택하세요.<br />
      불가침 기한 다음 달부터 선포 가능합니다.<br />
      현재 제의가 불가능한 국가는
      <span style="color: red">붉은색</span>으로 표시됩니다.<br />
    </div>
    <div class="row">
      <div class="col-4 col-lg-3">
        국가 :
        <SelectNation v-model="selectedNationID" :nations="nationList" :searchable="searchable" />
      </div>
      <div class="col-5 col-lg-3">
        기간 :
        <div class="input-group">
          <b-form-select v-model="selectedYear" class="text-end selectedYear">
            <b-form-select-option v-for="yearP in maxYear - minYear + 1" :key="yearP" :value="yearP + minYear - 1">
              {{ yearP + minYear - 1 }}
            </b-form-select-option>
          </b-form-select>
          <span class="input-group-text px-2">년</span>
          <b-form-select v-model="selectedMonth" class="text-center">
            <b-form-select-option v-for="month in 12" :key="month" :value="month">
              {{ month }}
            </b-form-select-option>
          </b-form-select>
          <span class="input-group-text px-2">월</span>
        </div>
      </div>
      <div class="col-3 col-lg-2 d-grid">
        <b-button @click="submit">
          {{ commandName }}
        </b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" type="chief" />
</template>

<script lang="ts">
declare const staticValues: {
  serverNick: string;
  serverID: string;
  mapName: string;
  commandName: string;
};

declare const procRes: {
  nationList: procNationList;
  startYear: number;
  minYear: number;
  maxYear: number;
  month: number;
};

declare const getCityPosition: () => CityPositionMap;
declare const formatCityInfo: (city: MapCityParsedRaw) => MapCityParsed;
</script>

<script lang="ts" setup>
import MapViewer, { type CityPositionMap, type MapCityParsed, type MapCityParsedRaw } from "@/components/MapViewer.vue";
import SelectNation from "@/processing/SelectNation.vue";
import { ref, watch, onMounted, provide } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { getProcSearchable, type procNationItem, type procNationList } from "../processingRes";
import type { MapResult } from "@/defs";
import { SammoAPI } from "@/SammoAPI";
import { getGameConstStore, type GameConstStore } from "@/GameConstStore";


const serverNick = staticValues.serverNick;
const serverID = staticValues.serverID;

const cityPosition = getCityPosition();
const formatCityInfoText = formatCityInfo;
const imagePath = window.pathConfig.gameImage;

const asyncReady = ref<boolean>(false);
const gameConstStore = ref<GameConstStore>();
provide("gameConstStore", gameConstStore);
const storeP = getGameConstStore().then((store) => {
  gameConstStore.value = store;
});

void Promise.all([storeP]).then(() => {
  asyncReady.value = true;
});

const nationList = new Map<number, procNationItem>();
for (const nationItem of procRes.nationList) {
  nationList.set(nationItem.id, nationItem);
}

const selectedNationID = ref(procRes.nationList[0].id);
const map = ref<MapResult>();

const minYear = procRes.minYear;
const maxYear = procRes.maxYear;

const selectedYear = ref(procRes.minYear);
const selectedMonth = ref(procRes.month);

async function submit(e: Event) {
  const event = new CustomEvent<Args>("customSubmit", {
    detail: {
      destNationID: selectedNationID.value,
      year: selectedYear.value,
      month: selectedMonth.value,
    },
  });
  unwrap(e.target).dispatchEvent(event);
}

const searchable = getProcSearchable();

const selectedCityObj = ref<MapCityParsed>();
const commandName = ref(staticValues.commandName);

watch(selectedCityObj, (city?: MapCityParsed) => {
  if (city === undefined) {
    return;
  }
  if (city.nationID === undefined) {
    return;
  }
  selectedNationID.value = city.nationID;
});

onMounted(async () => {
  try {
    map.value = await SammoAPI.Global.GetMap({ neutralView: 0, showMe: 1 });
  } catch (e) {
    console.error(e);
  }
});
</script>

<style lang="scss" scoped>
.selectedYear {
  width: 32%;
}
</style>
