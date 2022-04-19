<template>
  <div
    :class="['city_base', `city_base_${city.id}`, `city_level_${city.level}`]"
    :style="cityPos"
    @mouseenter="silent"
    @mouseleave="silent"
  >
    <a
      class="city_link"
      :data-text="city.text"
      :data-nation="city.nation"
      :data-id="city.id"
      :href="props.href"
      :style="{
        cursor: city.clickable ? 'pointer' : 'default',
      }"
      @click="clicked"
      @touchend="touchend"
      @mouseenter="mouseenter"
      @mouseleave="mouseleave"
    >
      <div
        class="city_img"
        :style="{
          backgroundColor: city.color,
        }"
      >
        <div :class="['city_filler', props.isMyCity ? 'my_city' : '']"></div>
        <div v-if="city.state > 0" :class="['city_state', `city_state_${getCityState()}`]"></div>
        <div v-if="city.nationID && city.nationID > 0" class="city_flag">
          <div v-if="city.isCapital" class="city_capital"></div>
        </div>
        <span class="city_detail_name">{{ city.name }}</span>
      </div>
    </a>
  </div>
</template>

<script lang="ts" setup>
import type { MapCityParsed } from "@/map";
import { ref, toRef, watch, type PropType } from "vue";
const emit = defineEmits<{
  (event: "click", evnet: MouseEvent | TouchEvent): void;
  (event: "mouseenter", e: MouseEvent): void;
  (event: "mouseleave", e: MouseEvent): void;
}>();
const props = defineProps({
  city: {
    type: Object as PropType<MapCityParsed>,
    required: true,
  },
  href: {
    type: String,
    default: undefined,
    required: false,
  },
  isMyCity: {
    type: Boolean,
    required: false,
    defeault: false,
  },
  isFullWidth: {
    type: Boolean,
    required: true,
  },
});


const city = toRef(props, "city");
const cityPos = ref({
  left: "0px",
  top: "0px",
});
watch(
  () => props.isFullWidth,
  (isFullWidth) => {
    const { x, y } = city.value;
    if (isFullWidth) {
      cityPos.value = {
        left: `${x - 20}px`,
        top: `${y - 15}px`,
      };
    } else {
      cityPos.value = {
        left: `${(x * 5) / 7 - 20}px`,
        top: `${(y * 5) / 7 - 18}px`,
      };
    }
  },
  { immediate: true }
);
function getCityState(): string {
  const state = city.value.state;
  if (state < 10) {
    return "good";
  }
  if (state < 40) {
    return "bad";
  }
  if (state < 50) {
    return "war";
  }
  return "wrong";
}

function clicked(event: MouseEvent) {
  emit("click", event);
}

function mouseenter(event: MouseEvent) {
  event.stopPropagation();
  emit("mouseenter", event);
}

function mouseleave(event: MouseEvent) {
  event.stopPropagation();
  emit("mouseleave", event);
}

function touchend(event: TouchEvent) {
  event.stopPropagation();
  emit("click", event);
}

function silent(event: MouseEvent) {
  event.stopPropagation();
}
</script>

<style lang="scss" scoped>
a,
div,
span {
  line-height: 1.3;
  font-size: 14px;
}
</style>
