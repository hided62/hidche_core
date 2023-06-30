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
      현재 도시의 인구를 인접 도시로 이동합니다.<br />
    </div>
    <div class="row">
      <div class="col-6 col-lg-4">
        도시 :
        <SelectCity v-model="selectedCityID" :cities="citiesMap" :searchable="searchable" />
      </div>
      <div class="col-6 col-lg-4">
        금 :
        <SelectAmount
          v-model="amount"
          :amountGuide="amountGuide"
          :step="10"
          :maxAmount="maxAmount"
          :minAmount="minAmount"
        />
      </div>
      <div class="col-4 col-lg-2 d-grid">
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
  currentCity: number;
  commandName: string;
};

declare const getCityPosition: () => CityPositionMap;
declare const formatCityInfo: (city: MapCityParsedRaw) => MapCityParsed;
</script>

<script lang="ts" setup>
import MapViewer, { type CityPositionMap, type MapCityParsed, type MapCityParsedRaw } from "@/components/MapViewer.vue";
import SelectCity from "@/processing/SelectCity.vue";
import SelectAmount from "@/processing/SelectAmount.vue";
import { ref, watch, onMounted, provide } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import {
  getProcSearchable,
} from "../processingRes";
import type { MapResult } from '@/defs';
import { SammoAPI } from '@/SammoAPI';
import { getGameConstStore, type GameConstStore } from '@/GameConstStore';

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

const amountGuide = [
  5000,
  10000,
  20000,
  30000,
  50000,
  100000,
];
const maxAmount = 100000;
const minAmount = 100;
const selectedCityID = ref(staticValues.currentCity);

const map = ref<MapResult>();
const citiesMap = ref(
  new Map<
    number,
    {
      name: string;
      info?: string;
    }
  >()
);
watch(gameConstStore, (store)=>{
  if(!store){
    return;
  }
  const tmpCitiesMap = new Map<
    number,
    {
      name: string;
      info?: string;
    }
  >();

  for(const city of Object.values(store.cityConst)){
    tmpCitiesMap.set(city.id, {
      name: city.name,
    });
  }
  citiesMap.value = tmpCitiesMap;
})

async function submit(e: Event) {
  const event = new CustomEvent<Args>("customSubmit", {
    detail: {
      destCityID: selectedCityID.value,
      amount: amount.value,
    },
  });
  unwrap(e.target).dispatchEvent(event);
}


const amount = ref(0);

const searchable = getProcSearchable();

const selectedCityObj = ref<MapCityParsed>();
const commandName = ref(staticValues.commandName);

watch(selectedCityObj, (city?: MapCityParsed) => {
  if (city === undefined) {
    return;
  }
  selectedCityID.value = city.id;
});

onMounted(async () => {
  try{
    map.value = await SammoAPI.Global.GetMap({neutralView:0, showMe: 1});
  }
  catch(e){
    console.error(e);
  }
});
</script>
