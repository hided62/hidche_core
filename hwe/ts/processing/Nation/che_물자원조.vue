<template>
  <TopBackBar v-model:searchable="searchable" :title="commandName" type="chief" />
  <div class="bg0">
    <MapLegacyTemplate
      v-model="selectedCityObj"
      :isDetailMap="false"
      :clickableAll="true"
      :neutralView="true"
      :useCachedMap="true"
      :mapName="mapName"
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
        <SelectNation v-model="selectedNationID" :nations="nationList" :searchable="searchable" />
      </div>
      <div class="col-6 col-md-0" />
      <div class="col-8 col-md-4">
        금 :
        <SelectAmount
          v-model="goldAmount"
          :amountGuide="amountGuide"
          :step="10"
          :maxAmount="maxAmount"
          :minAmount="minAmount"
        />
      </div>
      <div class="col-8 col-md-4">
        쌀 :
        <SelectAmount
          v-model="riceAmount"
          :amountGuide="amountGuide"
          :step="10"
          :maxAmount="maxAmount"
          :minAmount="minAmount"
        />
      </div>
      <div class="col-4 col-md-2 d-grid">
        <b-button variant="primary" @click="submit">
          {{ commandName }}
        </b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" type="chief" />
</template>

<script lang="ts">
import MapLegacyTemplate, { type MapCityParsed } from "@/components/MapLegacyTemplate.vue";
import SelectNation from "@/processing/SelectNation.vue";
import SelectAmount from "@/processing/SelectAmount.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { getProcSearchable, type procNationItem, type procNationList } from "../processingRes";
declare const staticValues: {
  mapName: string;
  commandName: string;
};

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
      mapName: staticValues.mapName,
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
      commandName: staticValues.commandName,
      submit,
    };
  },
  watch: {
    selectedCityObj(city: MapCityParsed) {
      if (!city.nationID) {
        return;
      }
      this.selectedNationID = city.nationID;
    },
  },
});
</script>
