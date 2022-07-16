<template>
  <div id="container">
    <TopBackBar :reloadable="true" title="부대 편성" @reload="refresh" />
    <div v-for="[troopID, troop] of troopList" :key="troopID">
      {{ troop.troopName }} {{ troop.turnTime }}
      <span v-for="member of troop.members" :key="member.no">
        {{ member.name }}
      </span>
    </div>
    <BottomBar />
  </div>
</template>

<script setup lang="ts">
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { onMounted, ref } from "vue";
import { SammoAPI } from "./SammoAPI";
import { useToast } from "bootstrap-vue-3";
import { unwrap } from "./util/unwrap";
import { isString } from "lodash";
import type { GeneralListItem } from "./defs/API/Nation";
import { merge2DArrToObjectArr } from "./util/merge2DArrToObjectArr";
import { convertIterableToMap } from '@/util/convertIterableToMap';
const toasts = unwrap(useToast());

type TroopInfo = {
  troopID: number,
  troopName: string,
  troopLeader: GeneralListItem,
  turnTime: string,
  members: GeneralListItem[],
}

const troopList = ref(new Map<number, TroopInfo>());
const generalList = ref(new Map<number, GeneralListItem>());

async function refresh() {
  try {
    const { column, list, permission, troops, env } = await SammoAPI.Nation.GeneralList();
    //빠른 턴 순서로 정렬되어있다.

    //XXX: 로직상 똑같은데....
    if (permission == 0) {
      const rawGeneralList = merge2DArrToObjectArr(column, list);
      generalList.value = convertIterableToMap(rawGeneralList.map((v) => {
        return { permission, st0: true, st1: false, st2: false, ...v };
      }), 'no');
    } else if (permission == 1) {
      const rawGeneralList = merge2DArrToObjectArr(column, list);
      generalList.value = convertIterableToMap(rawGeneralList.map((v) => {
        return { permission, st0: true, st1: true, st2: false, ...v };
      }), 'no');
    } else if ([2, 3, 4].includes(permission)) {
      const rawGeneralList = merge2DArrToObjectArr(column, list);
      generalList.value = convertIterableToMap(rawGeneralList.map((v) => {
        return { permission, st0: true, st1: true, st2: true, ...v };
      }), 'no');
    } else {
      throw `?? ${permission}`;
    }

    troopList.value = new Map();

    for (const { id: troopLeaderID, name: troopName, turntime: troopTurntime } of troops) {
      if (!generalList.value.has(troopLeaderID)) {
        toasts.warning({
          'title': '경고',
          'body': `${troopName} 부대장(${troopLeaderID})이 아국 소속이 아닌 것 같습니다.`
        });
        continue;
      }
      troopList.value.set(troopLeaderID, {
        troopID: troopLeaderID,
        troopName,
        troopLeader: unwrap(generalList.value.get(troopLeaderID)),
        turnTime: troopTurntime,
        members: [],
      });
    }

    for (const general of generalList.value.values()) {
      const troopID = general.troop;
      if (!troopID) {
        continue;
      }
      const troop = troopList.value.get(troopID);
      if (troop === undefined) {
        console.error(`부대 소속 오류: ${general.no} in ${troopID}`);
        continue;
      }
      troop.members.push(general);
    }
  }
  catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: '오류',
        body: e
      });
    }
    console.error(e);
    return;
  }

}

onMounted(() => {
  void refresh();
});

</script>
