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
    <template #option="props">
      <span
        :style="{
          color: props.option.notAvailable ? 'red' : undefined,
        }"
      >
        {{ props.option.title }}
        <span v-if="props.option.info">({{ props.option.info }})</span>
        {{ props.option.notAvailable ? "(불가)" : undefined }}
      </span>
    </template>
    <template #singleLabel="props">
      <span
        :style="{
          color: props.option.notAvailable ? 'red' : undefined,
        }"
      >
        {{ props.option.simpleName }}
        {{ props.option.notAvailable ? "(불가)" : undefined }}</span
      >
    </template>
  </v-multiselect>
</template>
<script lang="ts">
import { convertSearch초성 } from "@/util/convertSearch초성";
import { defineComponent, type PropType } from "vue";
import type { procNationItem } from "./processingRes";

type SelectedNation = {
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
    nations: {
      type: Map as PropType<Map<number, procNationItem>>,
      required: true,
    },
    searchable: {
      type: Boolean,
      required: false,
      default: true,
    },
  },
  emits: ["update:modelValue"],
  data() {
    const forFind = [];
    const targets = new Map<number, SelectedNation>();
    let selectedNation;
    for (const nationItem of this.nations.values()) {
      const obj: SelectedNation = {
        value: nationItem.id,
        title: nationItem.name,
        info: nationItem.info,
        simpleName: nationItem.name,
        notAvailable: nationItem.notAvailable,
        searchText: convertSearch초성(nationItem.name).join("|"),
      };
      if (nationItem.id == this.modelValue) {
        selectedNation = obj;
      }
      forFind.push(obj);
      targets.set(nationItem.id, obj);
    }
    return {
      selectedNation,
      forFind,
      targets,
    };
  },
  watch: {
    modelValue(val: number) {
      const target = this.targets.get(val);
      this.selectedNation = target;
    },
    selectedNation(val: SelectedNation) {
      this.$emit("update:modelValue", val.value);
    },
  },
});
</script>
