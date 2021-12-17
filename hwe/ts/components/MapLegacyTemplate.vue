<template>
  <div
    :id="uuid"
    :class="['world_map', `map_theme_${mapTheme}`, 'draw_required']"
  >
    <div
      class="map_title obj_tooltip"
      data-bs-toggle="tooltip"
      data-bs-placement="top"
      data-tooltip-class="map_title_tooltiptext"
    >
      <span class="map_title_text"> </span>
      <span class="tooltiptext"></span>
    </div>
    <div class="map_body">
      <div class="map_bglayer1"></div>
      <div class="map_bglayer2"></div>
      <div class="map_bgroad"></div>
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
      <div class="city_name"></div>
      <div class="nation_name"></div>
    </div>
  </div>
</template>
<script lang="ts">
import { reloadWorldMap, loadMapOption, MapCityParsed } from "@/map";
import { defineComponent, onMounted, PropType, ref } from "vue";
import { v4 as uuidv4 } from "uuid";
export type { MapCityParsed };
export default defineComponent({
  props: {
    mapTheme: {
      type: String,
      required: true,
    },
    isDetailMap: { type: Boolean, default: undefined },
    clickableAll: { type: Boolean, default: undefined },
    selectCallback: {
      type: Function as PropType<loadMapOption["selectCallback"]>,
      required: false,
    },
    hrefTemplate: { type: String, default: undefined },
    useCachedMap: { type: Boolean, default: undefined },

    year: { type: Number, default: undefined },
    month: { type: Number, default: undefined },
    aux: Object as PropType<loadMapOption["aux"]>,
    neutralView: { type: Boolean, default: undefined },
    showMe: { type: Boolean, default: undefined },

    targetJson: { type: String, default: undefined },
    reqType: {
      type: String as PropType<loadMapOption["reqType"]>,
      default: undefined,
    },
    dynamicMapTheme: { type: Boolean, default: undefined },
    callback: {
      type: Function as PropType<loadMapOption["callback"]>,
      default: undefined,
    },
    startYear: { type: Number, default: undefined },

    modelValue: {
      type: Object as PropType<MapCityParsed>,
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

      console.log(option);
      await reloadWorldMap(option, `#${uuid}`);
    });

    return {
      uuid,
    };
  },
});
</script>
