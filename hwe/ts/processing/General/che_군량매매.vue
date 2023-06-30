<template>
  <TopBackBar :title="commandName" />
  <div class="bg0">
    <div>자신의 군량을 사거나 팝니다.<br /></div>
    <div class="row">
      <div class="col-2 col-lg-1">
        군량을 :
        <b-button-group>
          <b-button :pressed="buyRice" @click="buyRice = true"> 삼 </b-button>
          <b-button :pressed="!buyRice" @click="buyRice = false"> 팜 </b-button>
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
  <BottomBar :title="commandName" />
</template>

<script lang="ts">
declare const staticValues: {
  commandName: string;
};

declare const procRes: {
  minAmount: number;
  maxAmount: number;
  amountGuide: number[];
};
</script>
<script setup lang="ts">
import SelectAmount from "@/processing/SelectAmount.vue";
import { ref } from "vue";
import type { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";

const commandName = staticValues.commandName;
const { minAmount, maxAmount, amountGuide } = procRes;

const amount = ref(1000);
const buyRice = ref(true);

async function submit(e: SubmitEvent) {
  if(!e.target){
    return;
  }
  const event = new CustomEvent<Args>("customSubmit", {
    detail: {
      amount: amount.value,
      buyRice: buyRice.value,
    },
  });
  e.target.dispatchEvent(event);
}
</script>
