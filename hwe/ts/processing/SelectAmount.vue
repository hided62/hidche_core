<template>
  <div class="input-group">
    <b-button v-if="maxAmount > 20000" class="btn-sm" @click="amount = Math.max(amount - 10000, minAmount)">
      -만
    </b-button>
    <b-button v-if="maxAmount > 2000" class="btn-sm" @click="amount = Math.max(amount - 1000, minAmount)">
      -천
    </b-button>
    <b-button v-if="maxAmount > 200" class="btn-sm" @click="amount = Math.max(amount - 100, minAmount)"> -백 </b-button>
    <input
      v-model.number="amount"
      type="number"
      class="form-control text-end"
      :max="maxAmount"
      :min="minAmount"
      :step="step"
      placeholder="금액"
    />
    <b-dropdown v-if="amountGuide" right text="" class="amount-dropdown">
      <b-dropdown-item v-for="guide in amountGuide" :key="guide" @click="amount = guide">
        <div class="text-end">
          {{ guide.toLocaleString() }}
        </div>
      </b-dropdown-item>
    </b-dropdown>
    <b-button v-if="maxAmount > 200" class="btn-sm" @click="amount = Math.min(amount + 100, maxAmount)"> +백 </b-button>
    <b-button v-if="maxAmount > 2000" class="btn-sm" @click="amount = Math.min(amount + 1000, maxAmount)">
      +천
    </b-button>
    <b-button v-if="maxAmount >= 10000" class="btn-sm" @click="amount = Math.min(amount + 10000, maxAmount)">
      +만
    </b-button>
  </div>
</template>
<script setup lang="ts">
import { ref, watch } from "vue";
import VueTypes from "vue-types";

const props = defineProps({
    modelValue: VueTypes.number.isRequired,
    minAmount: VueTypes.number.isRequired,
    maxAmount: VueTypes.number.isRequired,
    amountGuide: VueTypes.arrayOf(Number).def([1000, 2000, 5000, 10000]),
    step: VueTypes.number.def(1),
  });

const emit = defineEmits<{
  (event: "update:modelValue", value: number): void;
}>();

const amount = ref(props.modelValue);

watch(
  () => props.modelValue,
  (value) => {
    amount.value = value;
  }
);

watch(amount, (value) => {
  emit("update:modelValue", value);
});
</script>

<style lang="scss">
.btn-group.amount-dropdown > .btn {
  border-radius: 0;
}

.btn-group.amount-dropdown .dropdown-menu.show {
  min-width: 6rem;
}
</style>
