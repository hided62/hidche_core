<template>
  <v-multiselect
    v-model="selectedColor"
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
    placeholder="색상 선택"
    :maxHeight="400"
    :searchable="false"
  >
    <template v-slot:option="props">
      <div
        :class="`sam-color-${props.option.title.slice(1)}`"
        :style="{
          margin: '-0.375rem -0.75rem',
        }"
      >
        <div
          class="sam-nation-own-bgcolor"
          :style="{
            padding: '0.375rem 0.75rem',
          }"
        >
          {{ props.option.title }}
        </div>
      </div>
    </template>
    <template v-slot:singleLabel="props">
      <div
        :class="`sam-color-${props.option.title.slice(1)}`"
        :style="{
          margin: '-0.375rem -0.75rem',
        }"
        ><div
          class="sam-nation-own-bgcolor"
          :style="{
            padding: '0.375rem 0.75rem',
          }"
          >{{ props.option.title }}</div
        ></div
      >
    </template>
  </v-multiselect>
</template>
<script lang="ts">
import { unwrap } from "@/util/unwrap";
import { defineComponent, PropType } from "vue";

type SelectedColor = {
  value: number;
  title: string;
};

export default defineComponent({
  props: {
    modelValue: {
      type: Number,
      required: true,
    },
    colors: {
      type: Array as PropType<string[]>,
      required: true,
    },
  },
  emits: ["update:modelValue"],
  watch: {
    modelValue(val: number) {
      const target = unwrap(this.targets.get(val));
      this.selectedColor = target;
    },
    selectedColor(val: SelectedColor) {
      this.$emit("update:modelValue", val.value);
    },
  },
  data() {
    const forFind = [];
    const targets = new Map<number, SelectedColor>();
    for (const [value, title] of this.colors.entries()) {
      const obj: SelectedColor = {
        value,
        title,
      };
      console.log(obj);
      forFind.push(obj);
      targets.set(value, obj);
    }
    let selectedColor = forFind[0];

    return {
      selectedColor,
      searchMode: false,
      forFind,
      targets,
    };
  },
});
</script>

<style scoped>
.multiselect__option {
  padding: 0;
}
</style>