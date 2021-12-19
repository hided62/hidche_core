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
      선택된 국가에 급습을 발동합니다.<br />
      선포, 전쟁중인 상대국에만 가능합니다.<br />
      상대 국가를 목록에서 선택하세요.<br />
      배경색은 현재 급습 불가능 국가는 <span style="color:red;">붉은색</span>으로
      표시됩니다.<br />
    </div>
    <div class="row">
      <div class="col-6 col-md-3">
        국가 :
        <NationSelect :nations="nations" v-model="selectedNationID" />
      </div>
      <div class="col-4 col-md-2 d-grid">
        <b-button @click="submit">{{ commandName }}</b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" />
</template>

<script lang="ts">
import MapLegacyTemplate, {
  MapCityParsed,
} from "@/components/MapLegacyTemplate.vue";
import NationSelect from "@/processing/NationSelect.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import {
  procNationItem,
  procNationList
} from "../processingRes";
declare const mapTheme: string;
declare const currentNation: number;
declare const commandName: string;

declare const procRes: {
  nations: procNationList,
};

export default defineComponent({
  components: {
    MapLegacyTemplate,
    NationSelect,
    TopBackBar,
    BottomBar,
  },
  watch: {
    selectedCityObj(city: MapCityParsed) {
      if(!city.nationID){
        return;
      }
      this.selectedNationID = city.nationID;
    },
  },
  setup() {
    const nations = new Map<number, procNationItem>();
    for (const nationItem of procRes.nations) {
      nations.set(nationItem.id, nationItem);
    }

    const selectedNationID = ref(currentNation);

    function selectedNation(nationID: number) {
      selectedNationID.value = nationID;
    }

    async function submit(e: Event) {
      const event = new CustomEvent<Args>("customSubmit", {
        detail: {
          destNationID: selectedNationID.value,
        },
      });
      unwrap(e.target).dispatchEvent(event);
    }

    return {
      mapTheme: ref(mapTheme),
      nations: ref(nations),
      selectedNationID,
      commandName,
      selectedNation,
      submit,
    };
  },
});
</script>
