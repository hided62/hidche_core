import { useLocalStorage, useMediaQuery } from "@vueuse/core";
import { keyScreenMode, type ScreenModeType } from "@/defs";
import { ref, watch } from "vue";

export const is1000pxMode = useMediaQuery('(min-width:900px');
export const isFullWidth = ref(true);
export const widthMode = useLocalStorage<ScreenModeType>(keyScreenMode, "auto")

function setWidthMode([widthMode, is1000pxMode]: [ScreenModeType, boolean]): void {
  if (widthMode == "1000px") {
    isFullWidth.value = true;
  }
  if (widthMode == "500px") {
    isFullWidth.value = false;
  }
  isFullWidth.value = is1000pxMode;
}
watch([widthMode, is1000pxMode], setWidthMode);
setWidthMode([widthMode.value, is1000pxMode.value]);