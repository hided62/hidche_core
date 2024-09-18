<template>
  <TopBackBar v-model:searchable="searchable" :title="commandName" :type="procEntryMode" />
  <div v-if="asyncReady" class="bg0">
    <div v-if="commandName == '부대 탈퇴 지시'">
      지정한 장수에게 부대 탈퇴를 지시합니다. <br />
      부대원만 가능합니다.<br />
    </div>
    <div class="row">
      <div class="col-12 col-lg-5">
        장수 :
        <SelectGeneral
          v-model="selectedGeneralID"
          :cities="citiesMap"
          :generals="generalList"
          :textHelper="textHelpGeneral"
          :searchable="searchable"
        />
      </div>
      <div class="col-3 col-lg-2 d-grid">
        <b-button variant="primary" @click="submit">
          {{ commandName }}
        </b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" :type="procEntryMode" />
</template>

<script lang="ts">
declare const procRes: {
  troops: procTroopList;
  cities: [number, string][];
  generals: procGeneralRawItemList;
  generalsKey: procGeneralKey[];
};

declare const staticValues: {
  commandName: string;
  entryInfo: ["General" | "Nation", unknown];
};
</script>
<script setup lang="ts">
import SelectGeneral from "@/processing/SelectGeneral.vue";
import { ref, watch, provide } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import {
  convertGeneralList,
  getProcSearchable,
  procTroopList,
  type procGeneralItem,
  type procGeneralKey,
  type procGeneralRawItemList,
} from "./processingRes";
import { getNPCColor } from "@/utilGame";
import { getGameConstStore, type GameConstStore } from '@/GameConstStore';

const asyncReady = ref<boolean>(false);
const generalList = convertGeneralList(procRes.generalsKey, procRes.generals);
const gameConstStore = ref<GameConstStore>();
provide("gameConstStore", gameConstStore);
const storeP = getGameConstStore().then((store) => {
  gameConstStore.value = store;
});
void Promise.all([storeP]).then(() => {
  asyncReady.value = true;
});

const selectedGeneralID = ref(generalList[0].no);
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
      destGeneralID: selectedGeneralID.value,
    },
  });
  unwrap(e.target).dispatchEvent(event);
}

const { commandName,entryInfo } = staticValues;
const searchable = getProcSearchable();

const procEntryMode: "chief" | "normal" = entryInfo[0] == "Nation" ? "chief" : "normal";

</script>
