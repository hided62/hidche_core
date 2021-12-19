<template>
  <TopBackBar :title="commandName" type="chief" />
  <div class="bg0">
    <MapLegacyTemplate
      :isDetailMap="false"
      :clickableAll="true"
      :neutralView="true"
      :useCachedMap="true"
      :mapTheme="mapTheme"
      v-model="selectedCityObj"
    />

    <div>
      선택된 도시로 아국 장수를 발령합니다.<br />
      아국 도시로만 발령이 가능합니다.<br />
      목록을 선택하거나 도시를 클릭하세요.<br />
    </div>
    <div class="row">
      <div class="col-12 col-md-6">
        장수 :
        <GeneralSelect
          :cities="citiesMap"
          :generals="generalList"
          :troops="troops"
          :textHelper="textHelpGeneral"
          v-model="selectedGeneralID"
        />
      </div>
      <div class="col-6 col-md-4">
        도시 :
        <CitySelect :cities="citiesMap" v-model="selectedCityID" />
      </div>
      <div class="col-4 col-md-2 d-grid">
        <b-button variant="primary" @click="submit">{{ commandName }}</b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" />
</template>

<script lang="ts">
import MapLegacyTemplate, {
  MapCityParsed,
} from "@/components/MapLegacyTemplate.vue";
import CitySelect from "@/processing/CitySelect.vue";
import GeneralSelect from "@/processing/GeneralSelect.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import {
  convertGeneralList,
  procGeneralItem,
  procGeneralKey,
  procGeneralRawItemList,
  procTroopList,
} from "../processingRes";
import { getNpcColor } from "@/common_legacy";
declare const mapTheme: string;
declare const currentCity: number;
declare const commandName: string;

declare const procRes: {
  distanceList: Record<number, number[]>;
  cities: [number, string][];
  troops: procTroopList;
  generals: procGeneralRawItemList;
  generalsKey: procGeneralKey[];
};

export default defineComponent({
  components: {
    MapLegacyTemplate,
    CitySelect,
    GeneralSelect,
    TopBackBar,
    BottomBar,
  },
  watch: {
    selectedCityObj(city: MapCityParsed) {
      this.selectedCityID = city.id;
    },
  },
  setup() {
    const citiesMap = new Map<
      number,
      {
        name: string;
        info?: string;
      }
    >();
    for (const [id, name] of procRes.cities) {
      citiesMap.set(id, { name });
    }

    const generalList = convertGeneralList(
      procRes.generalsKey,
      procRes.generals
    );
    const selectedCityID = ref(currentCity);

    function selectedCity(cityID: number) {
      selectedCityID.value = cityID;
    }

    const selectedGeneralID = ref(generalList[0].no);

    function textHelpGeneral(gen: procGeneralItem): string{
      const troops = (!gen.troopID)?'':`,${procRes.troops[gen.troopID].name}`;
      const nameColor = getNpcColor(gen.npc);
      const name = nameColor?`<span style="color:${nameColor}">${gen.name}</span>`:gen.name;
      return `${name} [${citiesMap.get(unwrap(gen.cityID))?.name}${troops}] (${gen.leadership}/${gen.leadership}/${gen.intel}) <병${unwrap(gen.crew).toLocaleString()}/훈${gen.train}/사${gen.atmos}>`;
    }

    async function submit(e: Event) {
      const event = new CustomEvent<Args>("customSubmit", {
        detail: {
          destCityID: selectedCityID.value,
          destGeneralID: selectedGeneralID.value,
        },
      });
      unwrap(e.target).dispatchEvent(event);

      //        title: `${gen.name}[${this.cities.get(gen.cityID)?.name}] (${gen.leadership}/${gen.leadership}/${gen.intel}) <병${gen.crew.toLocaleString()}/훈${gen.train}/사${gen.atmos}`,

    }

    return {
      mapTheme: ref(mapTheme),
      citiesMap: ref(citiesMap),
      selectedCityID,
      selectedGeneralID,
      selectedCityObj: ref(undefined as MapCityParsed | undefined),
      distanceList: procRes.distanceList,
      troops: procRes.troops,
      generalList,
      commandName,
      selectedCity,
      textHelpGeneral,
      submit,
    };
  },
});
</script>
