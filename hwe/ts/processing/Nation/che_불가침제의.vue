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
      타국에게 불가침을 제의합니다.<br />
      제의할 국가를 목록에서 선택하세요.<br />
      불가침 기한 다음 달부터 선포 가능합니다.<br />
      현재 제의가 불가능한 국가는
      <span style="color: red">붉은색</span>으로 표시됩니다.<br />
    </div>
    <div class="row">
      <div class="col-4 col-md-3">
        국가 :
        <SelectNation :nations="nationList" v-model="selectedNationID" />
      </div>
      <div class="col-5 col-md-3">
        기간 :
        <div class="input-group">
          <b-form-select class="text-end selectedYear" v-model="selectedYear"
            ><b-form-select-option
              v-for="yearP in maxYear - minYear + 1"
              :key="yearP"
              :value="yearP + minYear - 1"
              >{{ yearP + minYear - 1 }}</b-form-select-option
            >
          </b-form-select>
          <span class="input-group-text px-2">년</span>
          <b-form-select class="text-center" v-model="selectedMonth"
            ><b-form-select-option v-for="month in 12" :key="month" :value="month">{{
              month
            }}</b-form-select-option>
          </b-form-select>
          <span class="input-group-text px-2">월</span>
        </div>
      </div>
      <div class="col-3 col-md-2 d-grid">
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
import SelectNation from "@/processing/SelectNation.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { procNationItem, procNationList } from "../processingRes";
declare const mapTheme: string;
declare const commandName: string;

declare const procRes: {
  nationList: procNationList;
  startYear: number;
  minYear: number;
  maxYear: number;
  month: number;
};

export default defineComponent({
  components: {
    MapLegacyTemplate,
    SelectNation,
    TopBackBar,
    BottomBar,
  },
  watch: {
    selectedCityObj(city: MapCityParsed) {
      if (!city.nationID) {
        return;
      }
      this.selectedNationID = city.nationID;
    },
  },
  setup() {
    const nationList = new Map<number, procNationItem>();
    for (const nationItem of procRes.nationList) {
      nationList.set(nationItem.id, nationItem);
    }

    const selectedNationID = ref(procRes.nationList[0].id);
    const selectedCityObj = ref(); //mapping용

    const selectedYear = ref(procRes.minYear);
    const selectedMonth = ref(procRes.month);

    function selectedNation(nationID: number) {
      selectedNationID.value = nationID;
    }

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

    return {
      ...procRes,
      selectedYear,
      selectedMonth,
      mapTheme: ref(mapTheme),
      nationList: ref(nationList),
      selectedCityObj,
      selectedNationID,
      commandName,
      selectedNation,
      submit,
    };
  },
});
</script>

<style lang="scss" scoped>
.selectedYear{
  width:32%;
}
</style>