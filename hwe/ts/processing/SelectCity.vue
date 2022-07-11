<template>
  <Multiselect
    v-model="selectedCity"
    :allow-empty="false"
    :options="citiesForFind"
    :group-select="false"
    label="searchText"
    track-by="value"
    :show-labels="false"
    selectLabel="선택(엔터)"
    selectGroupLabel=""
    selectedLabel="선택됨"
    deselectLabel="해제(엔터)"
    deselectGroupLabel=""
    placeholder="도시 선택"
    :maxHeight="400"
    :searchable="searchable"
  >
    <template #option="prop"
      ><span
        :style="{
          color: prop.option.notAvailable ? 'red' : undefined,
        }"
      >
        {{ prop.option.title }}
        <span v-if="prop.option.info">({{ prop.option.info }})</span>
        {{ prop.option.notAvailable ? "(불가)" : undefined }}</span
      >
    </template>
    <template #singleLabel="prop">
      <span
        :style="{
          color: prop.option.notAvailable ? 'red' : undefined,
        }"
        >{{ prop.option.simpleName }} {{ prop.option.notAvailable ? "(불가)" : undefined }}</span
      >
    </template>
  </Multiselect>
</template>
<script setup lang="ts">
import { Multiselect } from "vue-multiselect";
import { convertSearch초성 } from "@/util/convertSearch초성";
import { onMounted, ref, watch, type PropType } from "vue";

type SelectedCity = {
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
  cities: {
    type: Map as PropType<Map<number, { name: string; info?: string }>>,
    required: true,
  },
  searchable: {
    type: Boolean,
    required: false,
    default: true,
  },
});

const emit = defineEmits<{
  (event: "update:modelValue", value: number): void;
}>();

const citiesForFind = ref<SelectedCity[]>([]);
const targets = new Map<number, SelectedCity>();
const selectedCity = ref<SelectedCity>();

onMounted(() => {
  citiesForFind.value = [];
  targets.clear();

  for (const [value, { name, info }] of props.cities.entries()) {
    const obj: SelectedCity = {
      value,
      title: name,
      info: info,
      simpleName: name,
      searchText: convertSearch초성(name).join("|"),
    };
    if (value == props.modelValue) {
      selectedCity.value = obj;
    }
    citiesForFind.value.push(obj);
    targets.set(value, obj);
  }
});

watch(
  () => props.modelValue,
  (value) => {
    if (targets.has(value)) {
      selectedCity.value = targets.get(value);
    }
  }
);

watch(selectedCity, (value) => {
  if (!value) {
    return;
  }
  emit("update:modelValue", value.value);
});
</script>
