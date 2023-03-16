<template>
  <div
    :class="['city_base', `city_base_${city.id}`, `city_level_${city.level}`]"
    :style="cityPos"
    @mouseenter="silent"
    @mouseleave="silent"
  >
    <div
      v-if="city.color"
      :class="`city_bg b${city.color.substring(1)}`"
      :style="{
        backgroundImage: `url(${imagePath}/b${city.color.substring(1).toUpperCase()}.png)`,
      }"
    ></div>

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
      @touchstart="touchstart"
      @touchmove="touchmove"
      @touchend="touchend"
      @mouseenter="mouseenter"
      @mouseleave="mouseleave"
    >
      <div class="city_img">
        <img :src="`${imagePath}/cast_${city.level}.gif`" />
        <div :class="['city_filler', props.isMyCity ? 'my_city' : '']"></div>

        <div v-if="city.nationID && city.nationID > 0" class="city_flag">
          <img :src="`${imagePath}/${city.supply ? 'f' : 'd'}${unwrap(city.color).substring(1).toUpperCase()}.gif`" />
          <div v-if="city.isCapital" class="city_capital">
            <img :src="`${imagePath}/event51.gif`" />
          </div>
        </div>
        <span class="city_detail_name">{{ city.name }}</span>
      </div>
      <div v-if="city.state > 0" class="city_state">
        <img :src="`${imagePath}/event${city.state}.gif`" />
      </div>
    </a>
  </div>
</template>

<script lang="ts" setup>
import type { MapCityParsed } from "@/map";
import { ref, toRef, watch, type PropType } from "vue";
import { unwrap } from "@/util/unwrap";
const emit = defineEmits<{
  (event: "click", e: MouseEvent | TouchEvent): void;
  (event: "mouseenter", e: MouseEvent): void;
  (event: "mouseleave", e: MouseEvent): void;
  (event: "touchleave", e: TouchEvent): void;
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
  imagePath: {
    type: String,
    required: true,
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

const imagePath = toRef(props, "imagePath");

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

let touchOnTrack = false;

function touchstart() {
  touchOnTrack = true;
}

function touchmove() {
  touchOnTrack = false;
}

function touchend(event: TouchEvent) {
  if (touchOnTrack) {
    event.stopPropagation();
    emit("click", event);
  }
  else{
    emit("touchleave", event);
  }
}

function silent(event: MouseEvent) {
  event.stopPropagation();
}
</script>

<style lang="scss" scoped>
.full_width_map {
  a,
  div,
  img,
  span {
    line-height: 1.3;
    font-size: 14px;
  }
}
.small_width_map {
  a,
  div,
  img {
    line-height: 1.3;
    font-size: 14px;
  }

  span {
    line-height: 1;
    font-size: 11px;
  }
}
</style>
