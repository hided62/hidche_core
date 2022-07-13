<template>
  <v-multiselect
    v-model="selectedNation"
    :allow-empty="false"
    :options="forFind"
    :group-select="false"
    label="searchText"
    track-by="value"
    :show-labels="false"
    selectLabel="선택(엔터)"
    selectGroupLabel=""
    selectedLabel="선택됨"
    deselectLabel="해제(엔터)"
    deselectGroupLabel=""
    placeholder="국가 선택"
    :maxHeight="400"
    :searchable="searchable"
  >
    <template #option="prop">
      <span
        :style="{
          color: prop.option.notAvailable ? 'red' : undefined,
        }"
      >
        {{ prop.option.title }}
        <span v-if="prop.option.info">({{ prop.option.info }})</span>
        {{ prop.option.notAvailable ? "(불가)" : undefined }}
      </span>
    </template>
    <template #singleLabel="prop">
      <span
        :style="{
          color: prop.option.notAvailable ? 'red' : undefined,
        }"
      >
        {{ prop.option.simpleName }}
        {{ prop.option.notAvailable ? "(불가)" : undefined }}</span
      >
    </template>
  </v-multiselect>
</template>
<script setup lang="ts">
import { convertSearch초성 } from "@/util/convertSearch초성";
import { onMounted, ref, watch, toRef, type PropType } from "vue";
import type { procNationItem } from "./processingRes";

type SelectedNation = {
  value: number;
  searchText: string;
  title: string;
  simpleName: string;
  info?: string;
  notAvailable?: boolean;
};

const props = defineProps({
  modelValue: {
    type: Number,
    required: true,
  },
  nations: {
    type: Map as PropType<Map<number, procNationItem>>,
    required: true,
  },
  searchable: {
    type: Boolean,
    required: false,
    default: true,
  },
});

const modelValue = toRef(props, 'modelValue');
watch(
  modelValue,
  (val: number) => {
    const target = targets.value.get(val);
    selectedNation.value = target;
  }
);

const emit = defineEmits<{
  (event: "update:modelValue", value: number): void;
}>();

const forFind = ref<SelectedNation[]>([]);
const targets = ref(new Map<number, SelectedNation>());

const selectedNation = ref<SelectedNation>();
watch(selectedNation, (val) => {
  if (val === undefined) {
    return;
  }
  emit("update:modelValue", val.value);
});

onMounted(() => {
  forFind.value = [];
  targets.value.clear();
  for (const nationItem of props.nations.values()) {
    const obj: SelectedNation = {
      value: nationItem.id,
      title: nationItem.name,
      info: nationItem.info,
      simpleName: nationItem.name,
      notAvailable: nationItem.notAvailable,
      searchText: convertSearch초성(nationItem.name).join("|"),
    };
    if (nationItem.id == props.modelValue) {
      selectedNation.value = obj;
    }
    forFind.value.push(obj);
    targets.value.set(nationItem.id, obj);
  }
});

</script>
