<template>
  <MapLegacyTemplate
    :isDetailMap="false"
    :clickableAll="true"
    :neutralView="true"
    :useCachedMap="true"
    :mapTheme="mapTheme"
    v-model="selectedCityObj"
  />
  <div>
    선택된 도시로 강행합니다.<br />
    최대 3칸내 도시로만 강행이 가능합니다.<br />
    목록을 선택하거나 도시를 클릭하세요.<br />
  </div>
  <div class="row">
    <div class="col">
      <CitySelect :cities="citiesMap" :modelValue="selectedCityID" />
    </div>
    <div class="col">
      <b-button @click="submit">{{ commandName }}</b-button>
    </div>
  </div>
  <CityBasedOnDistance
    :citiesMap="citiesMap"
    :distanceList="distanceList"
    @selected="selected"
  />
</template>

<script lang="ts">
import "@/../css/map.css";
import MapLegacyTemplate, {
  MapCityParsed,
} from "@/components/MapLegacyTemplate.vue";
import CitySelect from "@/processing/CitySelect.vue";
import CityBasedOnDistance from "@/processing/CitiesBasedOnDistance.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import { Args } from "@/processing/args";
declare const mapTheme: string;
declare const cities: [number, string][];
declare const currentCity: number;
declare const distanceList: Record<number, number[]>;
declare const commandName: string;
export default defineComponent({
  name: "che_강행",
  components: {
    MapLegacyTemplate,
    CitySelect,
    CityBasedOnDistance,
  },
  watch: {
    selectedCityObj(city: MapCityParsed) {
      this.selectedCityID = city.id;
    },
  },
  setup() {
    console.log("start!");
    const citiesMap = new Map<
      number,
      {
        name: string;
        info?: string;
      }
    >();
    for (const [id, name] of cities) {
      citiesMap.set(id, { name });
    }
    console.log(citiesMap);

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
      distanceList,
      commandName,
      selected,
      submit,
    };
  },
});
</script>
