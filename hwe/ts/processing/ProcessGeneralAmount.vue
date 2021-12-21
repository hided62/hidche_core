<template>
  <TopBackBar :title="commandName" type="chief" />
  <div class="bg0">
    <div v-if="commandName == '몰수'">
      장수의 자금이나 군량을 몰수합니다.<br />
      몰수한것은 국가재산으로 귀속됩니다.<br />
    </div>
    <div v-else-if="commandName == '포상'">
      국고로 장수에게 자금이나 군량을 지급합니다.<br />
    </div>
    <div v-else-if="commandName == '증여'">
      자신의 자금이나 군량을 다른 장수에게 증여합니다.<br>
    </div>
    <div class="row">
      <div class="col-12 col-md-5">
        장수 :
        <SelectGeneral
          :cities="citiesMap"
          :generals="generalList"
          v-model="selectedGeneralID"
          :textHelper="textHelpGeneral"
        />
      </div>
      <div class="col-2 col-md-1">
        자원 :
        <b-button-group>
          <b-button :pressed="isGold" @click="isGold=true">금</b-button>
          <b-button :pressed="!isGold" @click="isGold=false">쌀</b-button>
        </b-button-group>
      </div>
      <div class="col-6 col-md-4">
        금액 :
        <SelectAmount
          :amountGuide="amountGuide"
          v-model="amount"
          :maxAmount="maxAmount"
          :minAmount="minAmount"
        />
      </div>
      <div class="col-4 col-md-2 d-grid">
        <b-button variant="primary" @click="submit">{{ commandName }}</b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" />
</template>

<script lang="ts">
import SelectGeneral from "@/processing/SelectGeneral.vue";
import SelectAmount from "@/processing/SelectAmount.vue";
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
} from "./processingRes";
import { getNpcColor } from "@/common_legacy";
declare const commandName: string;

declare const procRes: {
  distanceList: Record<number, number[]>;
  cities: [number, string][];
  generals: procGeneralRawItemList;
  generalsKey: procGeneralKey[];
  minAmount: number;
  maxAmount: number;
  amountGuide: number[];
};

export default defineComponent({
  components: {
    SelectGeneral,
    SelectAmount,
    TopBackBar,
    BottomBar,
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
    const amount = ref(1000);
    const isGold = ref(true);

    const selectedGeneralID = ref(generalList[0].no);

    function textHelpGeneral(gen: procGeneralItem): string {
      const nameColor = getNpcColor(gen.npc);
      const name = nameColor
        ? `<span style="color:${nameColor}">${gen.name}</span>`
        : gen.name;
      return `${name} (금${unwrap(gen.gold).toLocaleString()}/쌀${unwrap(gen.rice).toLocaleString()}) (${
        gen.leadership
      }/${gen.leadership}/${gen.intel})`;
    }

    async function submit(e: Event) {
      const event = new CustomEvent<Args>("customSubmit", {
        detail: {
          amount: amount.value,
          isGold: isGold.value,
          destGeneralID: selectedGeneralID.value,
        },
      });
      unwrap(e.target).dispatchEvent(event);
    }

    return {
      amount,
      isGold,
      selectedGeneralID,
      citiesMap: ref(citiesMap),
      distanceList: procRes.distanceList,
      minAmount: ref(procRes.minAmount),
      maxAmount: ref(procRes.maxAmount),
      amountGuide: procRes.amountGuide,
      generalList,
      commandName,
      textHelpGeneral,
      submit,
    };
  },
});
</script>
