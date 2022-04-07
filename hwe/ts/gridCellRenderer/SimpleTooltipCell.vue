<template>
  <span v-if="props.params.value == null">?</span>
  <span v-else-if="params.iActionMap" v-b-tooltip.hover :title="params.iActionMap[props.params.value].info ?? ''">{{
    params.iActionMap[props.params.value].name
  }}</span>
  <span v-else-if="params.info" v-b-tooltip.hover :title="params.info">{{ displayValue }}</span>
  <span v-else>{{ displayValue }}</span>
</template>

<script lang="ts" setup>
import type { GameIActionInfo } from "@/defs/GameObj";
import type { ValueFormatterParams } from "ag-grid-community";
import { isNumber, isString } from "lodash";
import { ref, watch, type PropType } from "vue";

const props = defineProps({
  params: {
    type: Object as PropType<
      ValueFormatterParams & {
        info?: string;
        iActionMap?: Record<string, GameIActionInfo>;
      }
    >,
    required: true,
  },
});

function convertValue(value: unknown): string {
  if (isString(value)) {
    return value;
  }
  if (isNumber(value)) {
    return value.toLocaleString();
  }
  return `${value}`;
}

const displayValue = ref<string>(convertValue(props.params.value));
watch(
  () => props.params,
  (newParams) => {
    displayValue.value = convertValue(newParams.value);
  }
);
</script>
