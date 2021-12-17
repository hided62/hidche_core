<template>
  <v-multiselect
    v-model="selectedCity"
    :allow-empty="false"
    :options="citiesForFind"
    :group-select="false"
    label="searchText"
    track-by="value"
    open-direction="bottom"
    :show-labels="false"
    selectLabel="선택(엔터)"
    selectGroupLabel=""
    selectedLabel="선택됨"
    deselectLabel="해제(엔터)"
    deselectGroupLabel=""
    placeholder="턴 선택"
    :maxHeight="400"
    :searchable="searchMode"
  >
    <template v-slot:option="props">
      {{ props.option.title }}
      <span v-if="props.option.info">({{ props.option.info }})</span>
    </template>
    <template v-slot:singleLabel="props">
      {{ props.option.simpleName }}
    </template>
  </v-multiselect>
</template>
<script lang="ts">
import { filter초성withAlphabet } from "@/util/filter초성withAlphabet";
import { defineComponent, PropType } from "vue";

type SelectedCity = {
  value: number;
  searchText: string;
  title: string;
  simpleName: string;
  info?: string;
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
    selectedCity(val: SelectedCity){
        this.$emit('update:modelValue', val.value);
    }
  },
  data() {
    const citiesForFind = [];
    const targets = new Map<number, SelectedCity>();
    let selectedCity;
    for (const [value, { name, info }] of this.cities.entries()) {
      const [filteredTextH, filteredTextA] = filter초성withAlphabet(name);
      const obj: SelectedCity = {
        value,
        title: name,
        info: info,
        simpleName: name,
        searchText: `${name} ${filteredTextH} ${filteredTextA}`
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
