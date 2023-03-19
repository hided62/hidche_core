<template>
  <table class="simple_nation_list">
    <thead>
      <tr>
        <th style="width: 44%">국명</th>
        <th style="width: 23%">국력</th>
        <th style="width: 15%">장수</th>
        <th style="width: 15%">속령</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="nation of nations" :key="nation.nation">
        <td>
          <span
            :style="{
              color: isBrightColor(nation.color) ? '#000' : '#fff',
              backgroundColor: nation.color,
            }"
            >{{ nation.name }}</span
          >
        </td>
        <td style="text-align: right">{{ nation.power.toLocaleString() }}</td>
        <td style="text-align: right">{{ nation.gennum.toLocaleString() }}</td>
        <td v-b-tooltip.hover="(nation.cities ?? []).join(', ')" style="text-align: right">
          {{ (nation.cities ?? []).length }}
        </td>
      </tr>
    </tbody>
    <tfoot></tfoot>
  </table>
</template>

<script lang="ts" setup>
import type { SimpleNationObj } from "@/defs";
import type { PropType } from "vue";
import { isBrightColor } from "@/util/isBrightColor";

defineProps({
  nations: {
    type: Array as PropType<SimpleNationObj[]>,
    required: true,
  },
});
</script>

<style lang="scss" scoped>
.simple_nation_list {
  width: 100%;

  thead {
    background-color: #cccccc;
    color: black;
    text-align: center;
  }
  th {
    border: 0;
    border-left: 1px solid gray;
    padding: 2px 6px;
  }

  td {
    border: 0;
    border-left: 1px solid gray;
    padding: 1px 6px;
    text-align: right;
  }

  td:first-child {
    text-align: left;
  }
}
</style>
