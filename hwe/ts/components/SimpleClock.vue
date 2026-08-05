<template>
  <span class="time-zone">{{ serverNow }}</span>
</template>

<script lang="ts" setup>
import { addMilliseconds } from "date-fns";
import { type PropType, ref, onMounted, onUnmounted, watch } from "vue";
import { formatTime } from "@/util/formatTime";
const props = defineProps({
  serverTime: {
    type: Object as PropType<Date>,
    required: false,
    default: new Date(),
  },
  timeFormat: {
    type: String,
    required: false,
    default: "HH:mm:ss",
  },
  running: {
    type: Boolean,
    default: true,
  },
});

const timeDiff = ref(0);
const serverNow = ref("");

watch(
  () => [props.serverTime, props.running] as const,
  ([newValue]) => {
    const clientNow = new Date();
    timeDiff.value = newValue.getTime() - clientNow.getTime();
    updateNow();
  }
);

let timer: ReturnType<typeof setTimeout> | undefined;
function updateNow() {
  if (timer !== undefined) {
    clearTimeout(timer);
    timer = undefined;
  }
  const serverNowObj = props.running
    ? addMilliseconds(new Date(), timeDiff.value)
    : props.serverTime;
  serverNow.value = formatTime(serverNowObj, props.timeFormat);
  if (props.running) {
    timer = setTimeout(() => {
      updateNow();
    }, 1000 - serverNowObj.getMilliseconds());
  }
}

onMounted(() => {
  const clientNow = new Date();
  timeDiff.value = props.serverTime.getTime() - clientNow.getTime();
  updateNow();
});

onUnmounted(() => {
  if (timer !== undefined) {
    clearTimeout(timer);
  }
});
</script>
