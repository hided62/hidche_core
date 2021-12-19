<template>
  <TopBackBar :title="commandName" />
  <div class="bg0">
    <MapLegacyTemplate
      :isDetailMap="false"
      :clickableAll="true"
      :neutralView="true"
      :useCachedMap="true"
      :mapTheme="mapTheme"
      v-model="selectedCityObj"
    />

    <div v-if="commandName == '강행'">
      선택된 도시로 강행합니다.<br />
      최대 3칸내 도시로만 강행이 가능합니다.<br />
      목록을 선택하거나 도시를 클릭하세요.<br />
    </div>
    <div v-else-if="commandName == '이동'">
      선택된 도시로 이동합니다.<br />
      인접 도시로만 이동이 가능합니다.<br />
      목록을 선택하거나 도시를 클릭하세요.<br />
    </div>
    <div v-else-if="commandName == '출병'">
      선택된 도시를 향해 침공을 합니다.<br />
      침공 경로에 적군의 도시가 있다면 전투를 벌입니다.<br />
      목록을 선택하거나 도시를 클릭하세요.<br />
    </div>
    <div class="row">
      <div class="col-4 col-md-2">
        도시:
        <CitySelect :cities="citiesMap" v-model="selectedCityID" />
      </div>
      <div class="col-4 col-md-2 d-grid">
        <b-button @click="submit">{{ commandName }}</b-button>
      </div>
    </div>
    <CityBasedOnDistance
      :citiesMap="citiesMap"
      :distanceList="distanceList"
      @selected="selected"
    />
  </div>
  <BottomBar :title="commandName" />
</template>

<script lang="ts">
import MapLegacyTemplate, {
  MapCityParsed,
} from "@/components/MapLegacyTemplate.vue";
import CitySelect from "@/processing/CitySelect.vue";
import CityBasedOnDistance from "@/processing/CitiesBasedOnDistance.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
declare const mapTheme: string;
declare const currentCity: number;
declare const commandName: string;
declare const procRes: {
  distanceList: Record<number, number[]>;
  cities: [number, string][];
};
export default defineComponent({
  components: {
    MapLegacyTemplate,
    CitySelect,
    CityBasedOnDistance,
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

    const selectedCityID = ref(currentCity);

    function selected(cityID: number) {
      selectedCityID.value = cityID;
    }

    async function submit(e: Event) {
      const event = new CustomEvent<Args>("customSubmit", {
        detail: {
          destCityID: selectedCityID.value,
        },
      });
      unwrap(e.target).dispatchEvent(event);
    }

    return {
      mapTheme: ref(mapTheme),
      citiesMap: ref(citiesMap),
      selectedCityID,
      selectedCityObj: ref(undefined as MapCityParsed | undefined),
      distanceList: procRes.distanceList,
      commandName,
      selected,
      submit,
    };
  },
});
</script>
