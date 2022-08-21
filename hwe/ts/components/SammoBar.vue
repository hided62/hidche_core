<template>
  <div
    v-b-tooltip.hover.top
    :title="`${props.percent.toLocaleString(undefined, { maximumFractionDigits: 2 })}%`"
    class="sammo-bar"
    :style="{
      height: `${props.height + 2}px`,
      position: 'relative',
      borderTop: 'solid 1px #888',
      borderBottom: 'solid 1px #333',
      margin: 'auto',
      width: props.width === undefined ? '100%' : props.width,
    }"
  >
    <div
      class="sammo-bar-base"
      :style="{
        position: 'absolute',
        left: '0',
        width: '100%',
        height: `${props.height}px`,
        backgroundImage: `url(${imagePath}/pr${props.height - 2}.gif)`,
        backgroundRepeat: 'repeat-x',
        backgroundPosition: 'center',
      }"
    ></div>
    <div
      class="sammo-bar-in"
      :style="{
        position: 'absolute',
        left: '0',
        width: `${clamp(props.percent, 0, 100)}%`,
        height: `${props.height}px`,
        backgroundImage: `url(${imagePath}/pb${props.height - 2}.gif)`,
        backgroundRepeat: 'repeat-x',
        backgroundPosition: 'left center',

      }"
    />
  </div>
</template>

<script lang="ts" setup>
import { clamp } from "lodash-es";
const imagePath = window.pathConfig.gameImage;
const props = defineProps<{
  height: 7 | 10;
  width?: string;
  percent: number;
}>();
</script>
