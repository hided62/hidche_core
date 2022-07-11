<template>
  <div v-for="(cityList, distance) in distanceList" :key="distance">
    {{ distance }}칸 떨어진 도시:
    <template v-for="(cityID, key) in cityList" :key="key">
      <template v-if="key !== 0"> , </template>
      <a
        :style="{ color: colorMap[distance as keyof typeof colorMap] ?? undefined, textDecoration:'underline' }"
        @click="emit('selected', cityID)"
        >{{ citiesMap.get(cityID)?.name }}</a
      >
    </template>
  </div>
</template>
<script setup lang="ts">
import type { PropType } from "vue";

defineProps({
  distanceList: {
    type: Object as PropType<Record<number, number[]>>,
    required: true,
  },
  citiesMap: {
    type: Object as PropType<Map<number, { name: string }>>,
    required: true,
  },
});

const emit = defineEmits<{
  (event: "selected", value: number): void;
}>();

const colorMap = {
  1: "magenta",
  2: "orange",
  3: "yellow",
};
</script>
