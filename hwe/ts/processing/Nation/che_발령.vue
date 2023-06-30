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
      선택된 도시로 아국 장수를 발령합니다.<br />
      아국 도시로만 발령이 가능합니다.<br />
      목록을 선택하거나 도시를 클릭하세요.<br />
    </div>
    <div class="row">
      <div class="col-12 col-lg-6">
        장수 :
        <SelectGeneral
          v-model="selectedGeneralID"
          :cities="citiesMap"
          :generals="generalList"
          :troops="troops"
          :textHelper="textHelpGeneral"
          :searchable="searchable"
        />
      </div>
      <div class="col-6 col-lg-4">
        도시 :
        <SelectCity v-model="selectedCityID" :cities="citiesMap" :searchable="searchable" />
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

declare const procRes: {
  troops: procTroopList;
  generals: procGeneralRawItemList;
  generalsKey: procGeneralKey[];
};

declare const getCityPosition: () => CityPositionMap;
declare const formatCityInfo: (city: MapCityParsedRaw) => MapCityParsed;
</script>

<script lang="ts" setup>
import MapViewer, { type CityPositionMap, type MapCityParsed, type MapCityParsedRaw } from "@/components/MapViewer.vue";
import SelectCity from "@/processing/SelectCity.vue";
import SelectGeneral from "@/processing/SelectGeneral.vue";
import { ref, watch, onMounted, provide } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import {
  convertGeneralList,
  getProcSearchable,
  type procGeneralItem,
  type procGeneralKey,
  type procGeneralRawItemList,
  type procTroopList,
} from "../processingRes";
import { getNPCColor } from "@/utilGame";
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

//TODO: onMount로 이전하고 장수 목록은 실시간으로 받아와야함
const generalList = convertGeneralList(procRes.generalsKey, procRes.generals);
const troops = procRes.troops;

const selectedGeneralID = ref(generalList[0].no);

function textHelpGeneral(gen: procGeneralItem): string {
  const troops = (()=>{
    if(!gen.troopID){
      return "";
    }

    const troopInfo = procRes.troops[gen.troopID];
    if(!troopInfo){
      return "";
    }
    const troopName = troopInfo.name;

    if(gen.no !== gen.troopID){
      return `,${troopName}`;
    }

    return `,<span style="text-decoration: underline;">${troopName}</span>`;
  })();
  const nameColor = getNPCColor(gen.npc);
  const name = nameColor ? `<span style="color:${nameColor}">${gen.name}</span>` : gen.name;
  return `${name} [${citiesMap.value.get(unwrap(gen.cityID))?.name}${troops}] (${gen.leadership}/${gen.strength}/${
    gen.intel
  }) <병${unwrap(gen.crew).toLocaleString()}/훈${gen.train}/사${gen.atmos}>`;
}

async function submit(e: Event) {
  const event = new CustomEvent<Args>("customSubmit", {
    detail: {
      destCityID: selectedCityID.value,
      destGeneralID: selectedGeneralID.value,
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
