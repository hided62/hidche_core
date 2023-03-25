<template>
  <div>
    <div>도시 정보</div>
    <div>{{cityID}}</div>

  </div>
</template>
<script lang="ts">
</script>
<script lang="ts" setup>
import { toRef } from 'vue';

const props = defineProps<{
  cityID?: number
}>()
const cityID = toRef(props, 'cityID');
</script>