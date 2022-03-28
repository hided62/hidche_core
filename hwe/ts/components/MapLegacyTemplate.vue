<template>
  <div :id="uuid" :class="['world_map', `map_theme_${mapName}`, 'draw_required']">
    <div
      class="map_title obj_tooltip"
      data-bs-toggle="tooltip"
      data-bs-placement="top"
      data-tooltip-class="map_title_tooltiptext"
    >
      <span class="map_title_text" />
      <span class="tooltiptext" />
    </div>
    <div class="map_body">
      <div class="map_bglayer1" />
      <div class="map_bglayer2" />
      <div class="map_bgroad" />
      <div class="map_button_stack">
        <button
          type="button"
          class="btn btn-primary map_toggle_cityname btn-sm btn-minimum"
          data-bs-toggle="button"
          aria-pressed="false"
          autocomplete="off"
        >
          도시명 표기</button
        ><br />
        <button
          type="button"
          class="btn btn-secondary map_toggle_single_tap btn-sm btn-minimum"
          data-bs-toggle="button"
          aria-pressed="false"
          autocomplete="off"
        >
          두번 탭 해 도시 이동
        </button>
      </div>
    </div>
    <div class="city_tooltip">
      <div class="city_name" />
      <div class="nation_name" />
    </div>
  </div>
</template>
<script lang="ts">
import "@/../css/map.css";
import { reloadWorldMap, type loadMapOption, type MapCityParsed } from "@/map";
import { defineComponent, onMounted, type PropType, ref } from "vue";
import { v4 as uuidv4 } from "uuid";
export type { MapCityParsed };
export default defineComponent({
  props: {
    mapName: {
      type: String,
      required: true,
    },
    isDetailMap: { type: Boolean, default: undefined, required: false },
    clickableAll: { type: Boolean, default: undefined, required: false },
    selectCallback: {
      type: Function as PropType<loadMapOption["selectCallback"]>,
      default: undefined,
      required: false,
    },
    hrefTemplate: { type: String, default: undefined, required: false },
    useCachedMap: { type: Boolean, default: undefined, required: false },

    year: { type: Number, default: undefined, required: false },
    month: { type: Number, default: undefined, required: false },
    aux: {
      type: Object as PropType<loadMapOption["aux"]>,
      default: undefined,
      required: false,
    },
    neutralView: { type: Boolean, default: undefined, required: false },
    showMe: { type: Boolean, default: undefined, required: false },

    targetJson: { type: String, default: undefined, required: false },
    reqType: {
      type: String as PropType<loadMapOption["reqType"]>,
      default: undefined,
    },
    dynamicMapTheme: { type: Boolean, default: undefined, required: false },
    callback: {
      type: Function as PropType<loadMapOption["callback"]>,
      default: undefined,
    },
    startYear: { type: Number, default: undefined, required: false },

    modelValue: {
      type: Object as PropType<MapCityParsed>,
      default: undefined,
      required: false,
    },
  },
  emits: ["update:modelValue", "loaded"],
  setup(props, { emit }) {
    const uuid = uuidv4();
    const modelValue = ref(props.modelValue);

    onMounted(async () => {
      const option: loadMapOption = {
        isDetailMap: props.isDetailMap,
        clickableAll: props.clickableAll,
        selectCallback: (city) => {
          modelValue.value = city;
          emit("update:modelValue", city);
        },
        hrefTemplate: props.hrefTemplate,
        useCachedMap: props.useCachedMap,

        year: props.year,
        month: props.month,
        aux: props.aux,
        neutralView: props.neutralView,
        showMe: props.showMe,

        targetJson: props.targetJson,
        reqType: props.reqType,
        dynamicMapTheme: props.dynamicMapTheme,
        callback: (a, rawObject) => {
          emit("loaded", [a, rawObject]);
        },

        startYear: props.startYear,
      };

      await reloadWorldMap(option, `#${uuid}`);
    });

    return {
      uuid,
    };
  },
});
</script>
