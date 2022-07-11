<template>
  <Multiselect
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
    <template #option="prop">
      <div
        :class="`sam-color-${prop.option.title.slice(1)}`"
        :style="{
          margin: '-0.375rem -0.75rem',
        }"
      >
        <div
          class="sam-nation-own-bgcolor"
          :style="{
            padding: '0.545rem 0.75rem',
          }"
        >
          {{ prop.option.title }}
        </div>
      </div>
    </template>
    <template #singleLabel="prop">
      <div
        :class="`sam-color-${prop.option.title.slice(1)}`"
        :style="{
          margin: '-0.25rem -0.75rem',
        }"
      >
        <div
          class="sam-nation-own-bgcolor"
          :style="{
            padding: '0.30rem 0.75rem',
            borderRadius: '0.25rem',
          }"
        >
          {{ prop.option.title }}
        </div>
      </div>
    </template>
  </Multiselect>
</template>
<script setup lang="ts">
import { Multiselect } from "vue-multiselect";
import { onMounted, ref, watch, type PropType } from "vue";

type SelectedColor = {
  value: number;
  title: string;
};

const props = defineProps({
  modelValue: {
    type: Number,
    required: true,
  },
  colors: {
    type: Array as PropType<string[]>,
    required: true,
  },
});

const emit = defineEmits<{
  (event: "update:modelValue", value: number): void;
}>();

const forFind = ref<SelectedColor[]>([]);
const targets = ref(new Map<number, SelectedColor>());
const selectedColor = ref<SelectedColor>();

watch(
  () => props.modelValue,
  (value) => {
    selectedColor.value = targets.value.get(value);
  }
);

watch(selectedColor, (value) => {
  if (!value) {
    return;
  }
  emit("update:modelValue", value.value);
});


onMounted(() => {
  forFind.value = [];
  targets.value = new Map();
  for (const [value, title] of props.colors.entries()) {
    const obj: SelectedColor = {
      value,
      title,
    };
    forFind.value.push(obj);
    targets.value.set(value, obj);
  }
  selectedColor.value = forFind.value[0];
});

</script>
