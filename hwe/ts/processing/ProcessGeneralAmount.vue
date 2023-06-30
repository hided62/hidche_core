<template>
  <TopBackBar v-model:searchable="searchable" :title="commandName" :type="procEntryMode" />
  <div class="bg0">
    <div v-if="commandName == '몰수'">
      장수의 자금이나 군량을 몰수합니다.<br />
      몰수한것은 국가재산으로 귀속됩니다.<br />
    </div>
    <div v-else-if="commandName == '포상'">국고로 장수에게 자금이나 군량을 지급합니다.<br /></div>
    <div v-else-if="commandName == '증여'">자신의 자금이나 군량을 다른 장수에게 증여합니다.<br /></div>
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
      <div class="col-2 col-lg-1">
        자원 :
        <b-button-group>
          <b-button :pressed="isGold" @click="isGold = true"> 금 </b-button>
          <b-button :pressed="!isGold" @click="isGold = false"> 쌀 </b-button>
        </b-button-group>
      </div>
      <div class="col-7 col-lg-4">
        금액 :
        <SelectAmount v-model="amount" :amountGuide="amountGuide" :maxAmount="maxAmount" :minAmount="minAmount" />
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
  distanceList: Record<number, number[]>;
  cities: [number, string][];
  generals: procGeneralRawItemList;
  generalsKey: procGeneralKey[];
  minAmount: number;
  maxAmount: number;
  amountGuide: number[];
};

declare const staticValues: {
  commandName: string;
  entryInfo: ["General" | "Nation", unknown];
};
</script>
<script setup lang="ts">
import SelectGeneral from "@/processing/SelectGeneral.vue";
import SelectAmount from "@/processing/SelectAmount.vue";
import { ref } from "vue";
import { unwrap } from "@/util/unwrap";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import {
  convertGeneralList,
  getProcSearchable,
  type procGeneralItem,
  type procGeneralKey,
  type procGeneralRawItemList,
} from "./processingRes";
import { getNPCColor } from "@/utilGame";

const citiesMap = ref(new Map<
  number,
  {
    name: string;
    info?: string;
  }
>());
for (const [id, name] of procRes.cities) {
  citiesMap.value.set(id, { name });
}

const generalList = convertGeneralList(procRes.generalsKey, procRes.generals);
const amount = ref(1000);
const isGold = ref(true);

const selectedGeneralID = ref(generalList[0].no);

function textHelpGeneral(gen: procGeneralItem): string {
  const nameColor = getNPCColor(gen.npc);
  const name = nameColor ? `<span style="color:${nameColor}">${gen.name}</span>` : gen.name;
  return `${name} (금${unwrap(gen.gold).toLocaleString()}/쌀${unwrap(gen.rice).toLocaleString()}) (${gen.leadership}/${
    gen.strength
  }/${gen.intel})`;
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

const { commandName,entryInfo } = staticValues;
const searchable = getProcSearchable();

const procEntryMode: "chief" | "normal" = entryInfo[0] == "Nation" ? "chief" : "normal";

const {
  minAmount,
  maxAmount,
  amountGuide
} = procRes;

</script>
