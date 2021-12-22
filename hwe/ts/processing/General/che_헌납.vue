<template>
  <TopBackBar :title="commandName" />
  <div class="bg0">
    <div>
      자신의 자금이나 군량을 국가 재산으로 헌납합니다.
    </div>
    <div class="row">
      <div class="col-2 col-md-1">
        자원 :
        <b-button-group>
          <b-button :pressed="isGold" @click="isGold = true">금</b-button>
          <b-button :pressed="!isGold" @click="isGold = false">쌀</b-button>
        </b-button-group>
      </div>
      <div class="col-7 col-md-4">
        금액 :
        <SelectAmount
          :amountGuide="amountGuide"
          v-model="amount"
          :maxAmount="maxAmount"
          :minAmount="minAmount"
        />
      </div>
      <div class="col-3 col-md-2 d-grid">
        <b-button variant="primary" @click="submit">{{ commandName }}</b-button>
      </div>
    </div>
  </div>
  <BottomBar :title="commandName" />
</template>

<script lang="ts">
import SelectAmount from "@/processing/SelectAmount.vue";
import { defineComponent, ref } from "vue";
import { unwrap } from "@/util/unwrap";
import { Args } from "@/processing/args";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
declare const commandName: string;

declare const procRes: {
  minAmount: number;
  maxAmount: number;
  amountGuide: number[];
};

export default defineComponent({
  components: {
    SelectAmount,
    TopBackBar,
    BottomBar,
  },
  setup() {
    const amount = ref(1000);
    const isGold = ref(true);


    async function submit(e: Event) {
      const event = new CustomEvent<Args>("customSubmit", {
        detail: {
          amount: amount.value,
          isGold: isGold.value,
        },
      });
      unwrap(e.target).dispatchEvent(event);
    }

    return {
      amount,
      isGold,
      minAmount: ref(procRes.minAmount),
      maxAmount: ref(procRes.maxAmount),
      amountGuide: procRes.amountGuide,
      commandName,
      submit,
    };
  },
});
</script>
