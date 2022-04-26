<template>
  <div
    v-b-tooltip.hover.top
    :title="`${props.percent}$`"
    class="sammo-bar"
    :style="{
      height: `${props.height}px`,
      backgroundImage: `url(${imagePath}/pr${props.height - 2}.png)`,
      backgroundRepeat: 'repeat-x',
      backgroundPosition: 'center',
      borderTop: 'solid 1px #888',
      borderBottom: 'solid 1px #333',
    }"
  >
    <div
      class="sammo-bar-in"
      :style="{
        height: `${props.height}px`,
        backgroundImage: `url(${imagePath}/pb${props.height - 2}.png)`,
        backgroundRepeat: 'repeat-x',
        backgroundPosition: 'left center',
        width: `${clamp(props.percent, 0, 100)}%`,
      }"
    />
  </div>
</template>

<script lang="ts" setup>
import { clamp } from "lodash";
const imagePath = window.pathConfig.gameImage;
const props = defineProps<{
  height: 7 | 10;
  percent: number;
}>();
</script>
