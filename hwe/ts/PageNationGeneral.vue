<template>
  <BContainer id="container" :toast="{ root: true }" class="pageNationGeneral bg0">
    <TopBackBar :title="title" :reloadable="true" :teleport-zone="toolbarID" @reload="reload" />
    <!-- eslint-disable-next-line vue/max-attributes-per-line -->
    <GeneralList
      v-if="asyncReady"
      :list="generalList"
      :troops="troopList"
      :env="envVal"
      :toolbarID="toolbarID"
      role="pageNationGeneral"
      :height="'fill'"
      :availableGeneralClick="true"
      @generalClick="openBattleCenter"
    />
    <!--<BottomBar />-->
  </BContainer>
</template>

<script lang="ts">
/*declare const staticValues: {
    serverNick: string,
    mapName: string,
    unitSet: string,
};*/
</script>
<script lang="ts" setup>
import TopBackBar from "@/components/TopBackBar.vue";
//import BottomBar from "@/components/BottomBar.vue";
import { BContainer } from "bootstrap-vue-3";
import { onMounted, provide, ref } from "vue";
import { SammoAPI } from "./SammoAPI";
import { merge2DArrToObjectArr } from "./util/merge2DArrToObjectArr";
import type { GeneralListItem, GeneralListResponse } from "./defs/API/Nation";
import GeneralList from "./components/GeneralList.vue";
import { type GameConstStore, getGameConstStore } from "./GameConstStore";

const generalList = ref<GeneralListItem[]>([]);
const troopList = ref<Record<number, string>>({});
const envVal = ref<GeneralListResponse["env"]>({
  year: 1,
  month: 1,
  turnterm: 1,
  turntime: "2022-02-22 22:22:22.22222",
  killturn: 80,
});

const toolbarID = "toolbar-id";

const gameConstStore = ref<GameConstStore>();
provide("gameConstStore", gameConstStore);

const asyncReady = ref(false);

const title = "세력 장수";

const storeP = getGameConstStore().then((store) => {
  gameConstStore.value = store;
});

void Promise.all([storeP]).then(() => {
  asyncReady.value = true;
});

async function reload() {
  try {
    const { column, list, permission, troops, env } = await SammoAPI.Nation.GeneralList();
    troopList.value = {};
    //XXX: 로직상 똑같은데....
    if (permission == 0) {
      const rawGeneralList = merge2DArrToObjectArr(column, list);
      generalList.value = rawGeneralList.map((v) => {
        return { permission, st0: true, st1: false, st2: false, ...v };
      });
    } else if (permission == 1) {
      const rawGeneralList = merge2DArrToObjectArr(column, list);
      generalList.value = rawGeneralList.map((v) => {
        return { permission, st0: true, st1: true, st2: false, ...v };
      });
    } else if ([2, 3, 4].includes(permission)) {
      const rawGeneralList = merge2DArrToObjectArr(column, list);
      generalList.value = rawGeneralList.map((v) => {
        return { permission, st0: true, st1: true, st2: true, ...v };
      });

      for (const [troopLeader, troopName] of troops) {
        troopList.value[troopLeader] = troopName;
      }
    } else {
      throw `?? ${permission}`;
    }
    envVal.value = env;
  } catch (e) {
    console.error(e);
    throw e;
  }
}

function openBattleCenter(generalID: number){
  window.open(`v_battleCenter.php?gen=${generalID}`)
}

onMounted(async () => {
  await reload();
});
</script>

<style lang="scss">
html,
body,
#app {
  height: 100%;
}

.pageNationGeneral {
  display: grid;
  //grid-template-rows: auto 1fr auto;
  grid-template-rows: auto 1fr;
  height: 100%;
}
</style>
