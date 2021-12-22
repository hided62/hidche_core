<template>
  <TopBackBar :title="commandName" type="chief" v-model:searchable="searchable" />
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
        <SelectNation :nations="nationList" v-model="selectedNationID" :searchable="searchable" />
      </div>
      <div class="col-6 col-md-0"></div>
      <div class="col-8 col-md-4">
        금 :
        <SelectAmount
          :amountGuide="amountGuide"
          v-model="goldAmount"
          :step="10"
          :maxAmount="maxAmount"
          :minAmount="minAmount"
        />
      </div>
      <div class="col-8 col-md-4">
        쌀 :
        <SelectAmount
          :amountGuide="amountGuide"
          v-model="riceAmount"
          :step="10"
          :maxAmount="maxAmount"
          :minAmount="minAmount"
        />
      </div>
      <div class="col-4 col-md-2 d-grid">
        <b-button variant="primary" @click="submit">{{ commandName }}</b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" type="chief" />
</template>

<script lang="ts">
import MapLegacyTemplate, {
  MapCityParsed,
} from "@/components/MapLegacyTemplate.vue";
import SelectNation from "@/processing/SelectNation.vue";
import SelectAmount from "@/processing/SelectAmount.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { getProcSearchable, procNationItem, procNationList } from "../processingRes";
declare const mapTheme: string;
declare const commandName: string;

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

export default defineComponent({
  components: {
    MapLegacyTemplate,
    SelectNation,
    SelectAmount,
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

    const goldAmount = ref(procRes.minAmount);
    const riceAmount = ref(procRes.minAmount);

    const selectedNationID = ref(procRes.nationList[0]?.id);
    const selectedCityObj = ref(); //mapping용

    async function submit(e: Event) {
      const event = new CustomEvent<Args>("customSubmit", {
        detail: {
          amountList: [goldAmount.value, riceAmount.value],
          destNationID: selectedNationID.value,
        },
      });
      unwrap(e.target).dispatchEvent(event);
    }

    return {
      searchable: getProcSearchable(),
      mapTheme,
      goldAmount,
      riceAmount,
      nationList,
      selectedNationID,
      selectedCityObj,
      currentNationLevel: procRes.currentNationLevel,
      levelInfo: procRes.levelInfo,
      minAmount: ref(procRes.minAmount),
      maxAmount: ref(procRes.maxAmount),
      amountGuide: procRes.amountGuide,
      commandName,
      submit,
    };
  },
});
</script>
