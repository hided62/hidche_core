<template>
  <div class="row form-group number-input-with-info">
    <label v-if="!right && title" class="col col-form-label">{{ title }}</label>
    <div class="col">
      <input
        ref="input"
        v-model.number="rawValue"
        type="number"
        :step="step ?? undefined"
        class="form-control f_tnum"
        :min="min ?? undefined"
        :max="max ?? undefined"
        :style="{ display: editmode ? undefined : 'none' }"
        @blur="onBlurNumber"
        @input="updateValue"
      />
      <input
        type="text"
        class="form-control f_tnum"
        :readonly="readonly"
        :value="printValue"
        :style="{ display: !editmode ? undefined : 'none' }"
        @focus="onFocusText"
      />
    </div>
    <label v-if="right && title" class="col col-form-label">{{ title }}</label>
  </div>
  <div style="text-align: right">
    <small class="form-text text-muted"><slot /></small>
  </div>
</template>
<script setup lang="ts">
import { clamp } from "lodash-es";
import { ref, watch } from "vue";

const props = defineProps({
  readonly: {
    type: Boolean,
    required: false,
    default: false,
  },
  int: {
    type: Boolean,
    required: false,
    default: true,
  },
  title: {
    type: String,
    required: false,
    default: null,
  },
  min: {
    type: Number,
    required: false,
    default: 0,
  },
  max: {
    type: Number,
    default: undefined,
    required: false,
  },
  step: {
    type: Number,
    default: undefined,
    required: false,
  },
  modelValue: {
    type: Number,
    default: 0,
  },
  right: {
    type: Boolean,
    required: false,
    default: false,
  },
});

const emit = defineEmits<{
  (event: "update:modelValue", value: number): void;
}>();

const editmode = ref(false);
const rawValue = ref(props.modelValue);
const printValue = ref(props.modelValue.toLocaleString());

watch(
  () => props.modelValue,
  (value) => {
    rawValue.value = value;
    printValue.value = value.toLocaleString();
  }
);

function updateValue() {
  if (props.readonly) {
    return;
  }
  let value = rawValue.value;
  if (props.int) {
    value = Math.round(value);
  }

  rawValue.value = value;

  printValue.value = value.toLocaleString();
  emit("update:modelValue", value);
}
const input = ref<HTMLInputElement>();
function onBlurNumber() {
  editmode.value = false;
  printValue.value = rawValue.value.toLocaleString();
  if (props.min !== undefined || props.max !== undefined) {
    const clampedValue = clamp(rawValue.value, props.min ?? rawValue.value, props.max ?? rawValue.value);
    if (clampedValue !== rawValue.value) {
      rawValue.value = clampedValue;
      updateValue();
    }
  }
}

function onFocusText() {
  if (props.readonly) {
    return;
  }
  editmode.value = true;
  setTimeout(() => {
    input.value?.focus();
  }, 0);
}
</script>
