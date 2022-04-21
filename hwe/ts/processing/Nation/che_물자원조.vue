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
      타국에게 원조합니다.<br />
      작위별로 금액 제한이 있습니다.<br /><br />
      <ul>
        <template v-for="({ text, amount }, level) in levelInfo" :key="level">
          <li>
            <span
              :style="{
                width: '4em',
                display: 'inline-block',
                ...(level != currentNationLevel
                  ? {}
                  : {
                      textDecoration: 'underline',
                      fontWeight: 'bold',
                    }),
              }"
              >{{ text }}</span
            >: {{ amount.toLocaleString() }}
          </li>
        </template>
      </ul>
      <br />
      원조할 국가를 목록에서 선택하세요.<br /><br />
    </div>
    <div class="row">
      <div class="col-6 col-md-3">
        국가 :
        <SelectNation v-model="selectedNationID" :nations="nationList" :searchable="searchable" />
      </div>
      <div class="col-6 col-md-0" />
      <div class="col-8 col-md-4">
        금 :
        <SelectAmount
          v-model="goldAmount"
          :amountGuide="amountGuide"
          :step="10"
          :maxAmount="maxAmount"
          :minAmount="minAmount"
        />
      </div>
      <div class="col-8 col-md-4">
        쌀 :
        <SelectAmount
          v-model="riceAmount"
          :amountGuide="amountGuide"
          :step="10"
          :maxAmount="maxAmount"
          :minAmount="minAmount"
        />
      </div>
      <div class="col-4 col-md-2 d-grid">
        <b-button variant="primary" @click="submit">
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
  currentNationLevel: number;
  levelInfo: Record<
    number,
    {
      text: string;
      amount: number;
    }
  >;
  minAmount: number;
  maxAmount: number;
  amountGuide: number[];
};

declare const getCityPosition: () => CityPositionMap;
declare const formatCityInfo: (city: MapCityParsedRaw) => MapCityParsed;
</script>

<script lang="ts" setup>
import MapViewer, { type CityPositionMap, type MapCityParsed, type MapCityParsedRaw } from "@/components/MapViewer.vue";
import SelectNation from "@/processing/SelectNation.vue";
import SelectAmount from "@/processing/SelectAmount.vue";
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

const goldAmount = ref(procRes.minAmount);
const riceAmount = ref(procRes.minAmount);

const currentNationLevel = procRes.currentNationLevel;
const levelInfo = procRes.levelInfo;
const minAmount = ref(procRes.minAmount);
const maxAmount = ref(procRes.maxAmount);
const amountGuide = procRes.amountGuide;

const selectedNationID = ref(procRes.nationList[0]?.id);

const map = ref<MapResult>();

async function submit(e: Event) {
  const event = new CustomEvent<Args>("customSubmit", {
    detail: {
      amountList: [goldAmount.value, riceAmount.value],
      destNationID: selectedNationID.value,
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
