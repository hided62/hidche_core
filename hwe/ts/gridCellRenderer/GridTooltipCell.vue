<template>
  <div>
    <div v-for="(rowValue, rowIdx) in props.params.cells" :key="rowIdx" class="row gx-1 m-0 cell-sp">
      <template v-for="colValue of rowValue" :key="colValue.target">
        <div v-if="params.data[colValue.target] == null">?</div>
        <div
          v-else-if="colValue.iActionMap"
          v-b-tooltip.hover="colValue.iActionMap[params.data[colValue.target] as string ].info??''"
          class="col"
        >
          {{ colValue.iActionMap[params.data[colValue.target] as string ].name }}
        </div>
        <div
          v-else-if="colValue.converter"
          v-b-tooltip.hover="colValue.converter(params.data)[1] ?? ''"
          class="col"
        >
          {{ colValue.converter(params.data)[0] }}
        </div>
        <div v-else v-b-tooltip.hover="colValue.info ?? ''" class="col" >
          {{params.data[colValue.target] as string}}
        </div>
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import type { GeneralListItemP2 } from "@/defs/API/Nation";
import type { GameIActionInfo } from "@/defs/GameObj";
import type { CellClassParams } from "ag-grid-community";

//제일 큰 타입 기준
export type GridCellInfo = {
  iActionMap?: Record<string, GameIActionInfo>;
  info?: string;
  converter?: (value: GeneralListItemP2) => [string, string?];
  target: keyof GeneralListItemP2;
};
</script>
<script lang="ts" setup>
import type { PropType } from "vue";
interface GenCellClassParams extends CellClassParams {
  data: GeneralListItemP2;
}

const props = defineProps({
  params: {
    type: Object as PropType<
      GenCellClassParams & {
        cells: GridCellInfo[][];
      }
    >,
    required: true,
  },
});
</script>
