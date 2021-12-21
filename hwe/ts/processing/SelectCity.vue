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
    :searchable="searchMode"
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
import { automata초성All } from "@/util/automata초성";
import { filter초성withAlphabet } from "@/util/filter초성withAlphabet";
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
      const [filteredTextH, filteredTextA] = filter초성withAlphabet(name);
      const [filteredTextHL1, filteredTextHL2] = automata초성All(filteredTextH);
      const obj: SelectedCity = {
        value,
        title: name,
        info: info,
        simpleName: name,
        searchText: `${name} ${filteredTextH} ${filteredTextA} ${filteredTextHL1} ${filteredTextHL2}`,
      };
      if (value == this.modelValue) {
        selectedCity = obj;
      }
      citiesForFind.push(obj);
      targets.set(value, obj);
    }
    return {
      selectedCity,
      searchMode: true,
      citiesForFind,
      targets,
    };
  },
});
</script>
