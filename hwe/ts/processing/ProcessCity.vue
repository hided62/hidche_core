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
    <div v-else-if="commandName == '첩보'">
      선택된 도시에 첩보를 실행합니다.<br />
      인접도시일 경우 많은 정보를 얻을 수 있습니다.<br />
      목록을 선택하거나 도시를 클릭하세요.<br />
    </div>
    <div v-else-if="commandName in { 화계: 1, 탈취: 1, 파괴: 1, 선동: 1 }">
      선택된 도시에 {{ commandName
      }}{{ JosaPick(commandName, "을") }} 실행합니다.<br />
      목록을 선택하거나 도시를 클릭하세요.<br />
    </div>
    <div v-else-if="commandName == '수몰'">
      선택된 도시에 수몰을 발동합니다.<br />
      전쟁중인 상대국 도시만 가능합니다.<br />
      목록을 선택하거나 도시를 클릭하세요.<br />
    </div>
    <div v-else-if="commandName == '백성동원'">
      선택된 도시에 백성을 동원해 성벽을 쌓습니다.<br />
      아국 도시만 가능합니다.<br />
      목록을 선택하거나 도시를 클릭하세요.<br />
    </div>
    <div v-else-if="commandName == '천도'">
      선택된 도시로 천도합니다.<br />
      현재 수도에서 연결된 도시만 가능하며, 1+2×거리만큼의 턴이 필요합니다.<br />
      목록을 선택하거나 도시를 클릭하세요.<br />
    </div>
    <div v-else-if="commandName == '허보'">
      선택된 도시에 허보를 발동합니다.<br />
      전쟁중인 상대국 도시만 가능합니다.<br />
      목록을 선택하거나 도시를 클릭하세요.<br />
    </div>
    <div v-else-if="commandName == '초토화'">
      선택된 도시를 초토화 시킵니다.<br />
      도시가 공백지가 되며, 도시의 인구, 내정 상태에 따라 상당량의 국고가
      확보됩니다.<br />
      국가의 수뇌들은 명성을 잃고, 모든 장수들은 배신 수치가 1 증가합니다.<br />
      목록을 선택하거나 도시를 클릭하세요.<br />
    </div>
    <div class="row">
      <div class="col-4 col-md-2">
        도시:
        <SelectCity :cities="citiesMap" v-model="selectedCityID" />
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
import SelectCity from "@/processing/SelectCity.vue";
import CityBasedOnDistance from "@/processing/CitiesBasedOnDistance.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { pick as JosaPick } from "@util/JosaUtil";
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
    SelectCity,
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
      JosaPick,
      selected,
      submit,
    };
  },
});
</script>
