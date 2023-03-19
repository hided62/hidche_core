<template>
  <span v-if="autorunMode.limit_minutes > 0" v-b-tooltip.hover="tooltipText" style="text-decoration: underline;">자율행동</span>
</template>
<script setup lang="ts">
import type { AutorunUserMode } from '@/defs/API/Global';
import type { Entries } from '@/util/Entries';
import { ref, toRef, watch } from 'vue';

type AutorunMode = {
    limit_minutes: number,
    options: Record<AutorunUserMode, number>,
  };

const props = defineProps<{
  autorunMode: AutorunMode
}>();

const tooltipText = ref("_");
const autorunMode = toRef(props, 'autorunMode');

function updateTooltipText(autorunMode: AutorunMode){
  const {options, limit_minutes} = autorunMode;
  const optionMap: Record<AutorunUserMode, string> = {
    'battle': '출병',
    'warp': '순간이동',
    'recruit': '징병',
    'recruit_high': '모병',
    'train': '훈련/사기진작',
    'chief': '사령턴',
    'develop': '내정',
  };

  const response = new Map<AutorunUserMode | 'limit_minutes', string>();
  for(const [option, value] of Object.entries(options) as Entries<typeof options>){
    if(value > 0){
      response.set(option, optionMap[option]);
    }
  }

  if(response.has('recruit_high')){
    response.delete('recruit');
  }

  if(limit_minutes >= 43200){
    response.set('limit_minutes', '항상 유효');
  }
  else if(limit_minutes % 60 == 0){
    response.set('limit_minutes', `${limit_minutes / 60}시간 유효`);
  }
  else{
    response.set('limit_minutes', `${limit_minutes}분 유효`);
  }

  const text = Array.from(response.values()).join(', ');
  tooltipText.value = text;
}

updateTooltipText(autorunMode.value);
watch(autorunMode, (newAutorunMode) => {
  updateTooltipText(newAutorunMode);
});

</script>