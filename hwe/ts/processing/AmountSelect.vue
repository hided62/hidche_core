<template>
  <div class="col-10 col-md-4">
    <div class="input-group">
      <b-button
        v-if="maxAmount > 30000"
        @click="amount = Math.max(amount - 10000, minAmount)"
        >-1만</b-button
      >
      <b-button
        v-if="maxAmount > 3000"
        @click="amount = Math.max(amount - 1000, minAmount)"
        >-1천</b-button
      >
      <b-button
        v-if="maxAmount > 300"
        @click="amount = Math.max(amount - 100, minAmount)"
        >-1백</b-button
      >
      <input
        type="number"
        class="form-control"
        :max="maxAmount"
        :min="minAmount"
        v-model="amount"
        placeholder="금액"
      />
      <b-dropdown right text="" class="amount-dropdown" v-if="amountGuide">
        <b-dropdown-item
          v-for="guide in amountGuide"
          :key="guide"
          @click="amount = guide"
          ><div class="text-end">
            {{ guide.toLocaleString() }}
          </div></b-dropdown-item
        >
      </b-dropdown>
      <b-button
        v-if="maxAmount > 300"
        @click="amount = Math.min(amount + 100, maxAmount)"
        >+1백</b-button
      >
      <b-button
        v-if="maxAmount > 3000"
        @click="amount = Math.min(amount + 1000, maxAmount)"
        >+1천</b-button
      >
      <b-button
        v-if="maxAmount > 30000"
        @click="amount = Math.min(amount + 10000, maxAmount)"
        >+1만</b-button
      >
    </div>
  </div>
</template>
<script lang="ts">
import { defineComponent, PropType } from "vue";

export default defineComponent({
  props: {
    modelValue: {
      type: Number,
      required: true,
    },
    minAmount: {
      type: Number,
      required: true,
    },
    maxAmount: {
      type: Number,
      required: true,
    },
    amountGuide: {
      type: Array as PropType<number[]>,
      reqquired: false,
    },
  },
  emits: ["update:modelValue"],
  watch: {
    amount(val: number) {
      this.$emit("update:modelValue", val);
    },
  },
  data() {
      return {
          amount: this.modelValue,
      }
  },
});
</script>


<style lang="scss">
.btn-group.amount-dropdown > .btn{
  border-radius: 0;
}

.btn-group.amount-dropdown .dropdown-menu.show {
  min-width: 6rem;
}
</style>