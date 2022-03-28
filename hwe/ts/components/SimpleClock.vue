<template>
    <span class="time-zone">{{serverNow}}</span>
</template>

<script lang="ts" setup>
import { addMilliseconds } from 'date-fns';
import { type PropType, ref, onMounted, watch } from 'vue';
import { formatTime } from '@/util/formatTime';
const props = defineProps({
    serverTime: {
        type: Object as PropType<Date>,
        required: false,
        default: new Date(),
    },
    timeFormat: {
        type: String,
        required: false,
        default: 'HH:mm:ss'
    }
})

const timeDiff = ref(0);
const serverNow = ref('');

watch(()=>props.serverTime, (newValue)=>{
    const clientNow = new Date();
    timeDiff.value = newValue.getTime() - clientNow.getTime();
});

function updateNow() {
  const serverNowObj = addMilliseconds(new Date(), timeDiff.value);
  serverNow.value = formatTime(serverNowObj, props.timeFormat);
  setTimeout(() => {
    updateNow();
  }, 1000 - serverNowObj.getMilliseconds());
}

onMounted(() => {
    const clientNow = new Date();
    timeDiff.value = props.serverTime.getTime() - clientNow.getTime();
    updateNow();
});
</script>