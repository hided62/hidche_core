<template>
  <v-multiselect
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
    <template v-slot:option="props"
      ><span
        :style="{
          color: props.option.notAvailable ? 'red' : undefined,
        }"
      >
        {{ props.option.title }}
        <span v-if="props.option.info">({{ props.option.info }})</span> {{ props.option.notAvailable ? "(불가)" : undefined }}</span
      >
    </template>
    <template v-slot:singleLabel="props">
      <span
        :style="{
          color: props.option.notAvailable ? 'red' : undefined,
        }"
      >{{ props.option.simpleName }} {{ props.option.notAvailable ? "(불가)" : undefined }}</span>
    </template>
  </v-multiselect>
</template>
<script lang="ts">
import { convertSearch초성 } from "@/util/convertSearch초성";
import { defineComponent, PropType } from "vue";

type SelectedCity = {
  value: number;
  searchText: string;
  title: string;
  simpleName: string;
  info?: string;
  notAvailable?: boolean;
};

export default defineComponent({
  props: {
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
  },
  emits: ["update:modelValue"],
  watch: {
    modelValue(val: number) {
      const target = this.targets.get(val);
      this.selectedCity = target;
    },
    selectedCity(val: SelectedCity) {
      this.$emit("update:modelValue", val.value);
    },
  },
  data() {
    const citiesForFind = [];
    const targets = new Map<number, SelectedCity>();
    let selectedCity;
    for (const [value, { name, info }] of this.cities.entries()) {
      const obj: SelectedCity = {
        value,
        title: name,
        info: info,
        simpleName: name,
        searchText: convertSearch초성(name).join('|'),
      };
      if (value == this.modelValue) {
        selectedCity = obj;
      }
      citiesForFind.push(obj);
      targets.set(value, obj);
    }
    return {
      selectedCity,
      citiesForFind,
      targets,
    };
  },
});
</script>
