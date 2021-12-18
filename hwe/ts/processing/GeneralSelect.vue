<template>
  <v-multiselect
    v-model="selectedGeneral"
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
    placeholder="장수 선택"
    :maxHeight="400"
    :searchable="searchMode"
  >
    <template v-slot:option="props">
      {{ props.option.title }}
    </template>
    <template v-slot:singleLabel="props">
      {{ props.option.simpleName }}
    </template>
  </v-multiselect>
</template>
<script lang="ts">
import { filter초성withAlphabet } from "@/util/filter초성withAlphabet";
import { defineComponent, PropType } from "vue";
import { procGeneralList, procTroopList } from './processingRes';

type SelectedGeneral = {
  value: number;
  searchText: string;
  title: string;
  simpleName: string;
};

export default defineComponent({
  props: {
    modelValue: {
      type: Number,
      required: true,
    },
    generals: {
      type: Array as PropType<procGeneralList>,
      required: true,
    },
    cities: {
      type: Map as PropType<Map<number, { name: string; info?: string }>>,
      required: true,
    },
    troops: {
      type: Object as PropType<procTroopList>,
    }
  },
  emits: ["update:modelValue"],
  watch: {
    modelValue(val: number) {
      const target = this.targets.get(val);
      this.selectedGeneral = target;
    },
    selectedGeneral(val: SelectedGeneral){
        this.$emit('update:modelValue', val.value);
    }
  },
  data() {
    const forFind = [];
    const targets = new Map<number, SelectedGeneral>();

    let selectedGeneral;

    for (const gen of this.generals) {
      const [filteredTextH, filteredTextA] = filter초성withAlphabet(gen.name);
      const obj: SelectedGeneral = {
        value: gen.no,
        title: `${gen.name}[${this.cities.get(gen.cityID)?.name}] (${gen.leadership}/${gen.leadership}/${gen.intel}) <병${gen.crew.toLocaleString()}/훈${gen.train}/사${gen.atmos}`,
        simpleName: gen.name,
        searchText: `${gen.name} ${filteredTextH} ${filteredTextA}`
      };
      if (gen.no == this.modelValue) {
        selectedGeneral = obj;
      }
      forFind.push(obj);
      targets.set(gen.no, obj);
    }
    return {
      selectedGeneral,
      searchMode: true,
      forFind,
      targets,
    };
  },
});
</script>
