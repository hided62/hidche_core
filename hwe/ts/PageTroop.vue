<template>
  <BContainer id="container" ref="container" :toast="{ root: true }">
    <TopBackBar :reloadable="true" title="부대 편성" @reload="refresh" />
    <div v-if="asyncReady && gameConstStore && me" id="troopList" class="bg0">
      <div v-for="[troopID, troop] of troopList" :key="troopID" class="troopItem">
        <div class="troopInfo">
          {{ troop.troopName }}<br />
          【 {{ gameConstStore.cityConst[troop.troopLeader.city].name }} 】
        </div>
        <div class="troopTurn">【턴】 {{ troop.turnTime.slice(14, 19) }}</div>
        <div class="troopLeaderIcon">
          <img height="64" width="64" :src="getIconPath(troop.troopLeader.imgsvr, troop.troopLeader.picture)" />
        </div>
        <div class="troopLeaderName">
          {{ troop.troopLeader.name }}
        </div>
        <div class="troopMembers">
          <template v-for="(member, idx) in troop.members" :key="member.no">
            <template v-if="idx != 0">, </template>
            <span
              v-if="member.troop == member.no"
              class="troopMember troopLeader"
              @mouseover="setPopup($event, member)"
              @mouseout="setPopup($event, undefined)"
            >
              {{ member.name }}
            </span>
            <span
              v-else-if="troop.troopLeader.city == member.city"
              class="troopMember"
              @mouseover="setPopup($event, member)"
              @mouseout="setPopup($event, undefined)"
            >
              {{ member.name }}
            </span>
            <span
              v-else
              class="troopMember troopDiffCityMemeber"
              @mouseover="setPopup($event, member)"
              @mouseout="setPopup($event, undefined)"
            >
              {{ member.name }} ({{ gameConstStore.cityConst[member.city].name }})
            </span>
          </template>
          ({{ troop.members.length }}명)
        </div>
        <div class="troopReservedCommand">
          <div v-for="(brief, idx) in troop.reservedCommandBrief" :key="idx">
            {{ `${idx + 1}: ${brief}` }}
          </div>
        </div>
        <div class="troopAction">
          <div
            v-if="tryTroopActionTarget.type === undefined || tryTroopActionTarget.targetTroop !== troop.troopID"
            class="d-grid"
          >
            <BButton v-if="!me.troop" variant="primary" @click="joinTroop(troop.troopID)">부대 탑승</BButton>
            <BButton
              v-if="me.troop == troop.troopID"
              :variant="me.troop == me.no ? 'danger' : 'primary'"
              @click="exitTroop()"
              >{{ me.no == me.troop ? "부대 해산" : "부대 탈퇴" }}</BButton
            >
            <BButton
              v-if="me.troop == troop.troopID && me.no == me.troop"
              @click="openKickTroopMemberDialog(troop)"
              >부대원 추방...</BButton
            >
            <BButton
              v-if="myPermission >= 4"
              variant="info"
              @click="openChangeTroopNameDialog(troop)"
              >부대명 변경...</BButton
            >
          </div>
          <div v-else-if="tryTroopActionTarget.type === 'changeName'" class="row gx-0">
            <div class="col-12 bg1 center" style="padding: 0.2em">부대명 변경</div>
            <div class="col-12 d-grid">
              <BFormInput v-model="newTroopName" :trim="true" type="text" />
            </div>
            <div class="col-6 d-grid">
              <BButton variant="secondary" @click="tryTroopActionTarget = { type: undefined, targetTroop: 0 }"
                >취소</BButton
              >
            </div>
            <div class="col-6 d-grid">
              <BButton variant="primary" @click="changeTroopName(troop)">변경</BButton>
            </div>
          </div>
          <div v-else-if="tryTroopActionTarget.type === 'kickMember'" class="row gx-0">
            <div class="col-12 bg1 center" style="padding: 0.2em">부대명 추방</div>
            <div class="col-12 d-grid">
              <BFormSelect v-model="kickTroopMemberID">
                <template v-for="member of troop.members" :key="member.no">
                  <BFormSelectOption v-if="member.no != troop.troopID" :value="member.no">
                    {{ member.name }}
                  </BFormSelectOption>
                </template>
              </BFormSelect>
            </div>
            <div class="col-6 d-grid">
              <BButton variant="secondary" @click="tryTroopActionTarget = { type: undefined, targetTroop: 0 }"
                >취소</BButton
              >
            </div>
            <div class="col-6 d-grid">
              <BButton variant="primary" @click="kickTroopMember(troop)">추방</BButton>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="asyncReady && gameConstStore && me" class="row additionalTroopOptions">
      <div v-if="me.troop == 0" class="col-6 col-md-3">
        <div class="row gx-0 makeNewTroop">
          <div class="bg1 col-12 center" style="font-size: 1.2em">부대 창설</div>
          <div class="troopNameField col-8 d-grid">
            <BFormInput v-model="newTroopName" :trim="true" type="text" />
          </div>
          <div class="col-4 d-grid">
            <BButton @click="makeTroop">부대 창설</BButton>
          </div>
        </div>
      </div>
    </div>
    <BottomBar />
    <div
      v-if="asyncReady && gameConstStore"
      id="generalPopup"
      :style="{ display: popupGeneralTarget ? 'block' : 'none', top: `${popupTop}px` }"
    >
      <div v-if="!popupGeneralTarget || !nationInfo"></div>
      <template v-else-if="popupGeneralTarget.st1">
        <GeneralBasicCard
          :general="popupGeneralTarget"
          :troopInfo="convBasicCardTroopInfo(popupGeneralTarget)"
          :nation="nationInfo"
          :turnTerm="turnTerm"
          :lastExecuted="lastExecuted"
        />
        <GeneralSupplementCard :general="popupGeneralTarget" :showCommandList="true" />
      </template>

      <GeneralLiteCard v-else :general="popupGeneralTarget" :nation="nationInfo" />
    </div>
  </BContainer>
</template>

<script setup lang="ts">
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { onMounted, provide, ref } from "vue";
import { SammoAPI } from "./SammoAPI";
import { BContainer, BButton, useToast, BFormInput, BFormSelect, BFormSelectOption } from "bootstrap-vue-3";
import { unwrap } from "./util/unwrap";
import { isString } from "lodash";
import type { GeneralListItem, GeneralListItemP1 } from "./defs/API/Nation";
import { merge2DArrToObjectArr } from "./util/merge2DArrToObjectArr";
import { convertIterableToMap } from "@/util/convertIterableToMap";
import { GameConstStore, getGameConstStore } from "./GameConstStore";
import { getIconPath } from "@/util/getIconPath";
import GeneralBasicCard from "./components/GeneralBasicCard.vue";
import type { NationStaticItem } from "./defs";
import GeneralLiteCard from "./components/GeneralLiteCard.vue";
import GeneralSupplementCard from "./components/GeneralSupplementCard.vue";
import { pick } from "./util/JosaUtil";
import { parseTime } from "./util/parseTime";
const toasts = unwrap(useToast());

const asyncReady = ref(false);
const gameConstStore = ref<GameConstStore>();
provide("gameConstStore", gameConstStore);
const storeP = getGameConstStore().then((store) => {
  gameConstStore.value = store;
});

void Promise.all([storeP]).then(() => {
  asyncReady.value = true;
});

const container = ref<HTMLElement>();

const popupGeneralTarget = ref<GeneralListItem>();
const popupTop = ref<number>(0);

function convBasicCardTroopInfo(general: GeneralListItemP1): { leader: GeneralListItemP1; name: string } | undefined {
  const troop = troopList.value.get(general.troop);
  if (!troop) {
    return undefined;
  }
  const troopLeader = troop.troopLeader;
  if (!troopLeader.st1) {
    return undefined;
  }
  return {
    leader: troopLeader,
    name: troop.troopName,
  };
}

function setPopup(e: MouseEvent, general: GeneralListItem | undefined) {
  if (general === undefined || !e.target) {
    popupTop.value = 0;
    popupGeneralTarget.value = undefined;
    return;
  }
  console.log("e", e);

  const target = e.target as HTMLElement;
  popupGeneralTarget.value = general;
  let parent = target.parentElement;
  console.log("parent", parent);
  while (parent !== null && !parent.classList.contains("troopMembers")) {
    parent = parent.parentElement;
  }

  if (parent === null) {
    popupTop.value = 0;
    popupGeneralTarget.value = undefined;
    return;
  }

  console.log(parent.offsetTop, parent.offsetHeight, parent);

  popupTop.value = parent.offsetTop + parent.offsetHeight;
}

type TroopInfo = {
  troopID: number;
  troopName: string;
  troopLeader: GeneralListItem;
  turnTime: string;
  reservedCommandBrief: string[];
  members: GeneralListItem[];
};

const me = ref<GeneralListItem>({} as GeneralListItem);
const myPermission = ref<0 | 1 | 2 | 3 | 4>(0);
const turnTerm = ref<number>(1);
const lastExecuted = ref<Date>(new Date());

const troopList = ref(new Map<number, TroopInfo>());
const generalList = ref(new Map<number, GeneralListItem>());
const nationInfo = ref<NationStaticItem>();

const newTroopName = ref("");
const kickTroopMemberID = ref(0);

type TroopAction = {
  type: "kickMember" | "changeName" | undefined;
  targetTroop: number;
};

const tryTroopActionTarget = ref<TroopAction>({
  type: undefined,
  targetTroop: 0,
});

async function refresh() {
  try {
    const generalListP = SammoAPI.Nation.GeneralList();
    const nationP = SammoAPI.Nation.GetNationInfo({});
    nationInfo.value = (await nationP).nation;
    const { column, list, permission, troops, env, myGeneralID } = await generalListP;
    turnTerm.value = env.turnterm;
    lastExecuted.value = parseTime(env.turntime);

    //빠른 턴 순서로 정렬되어있다.

    //XXX: 로직상 똑같은데....
    if (permission == 0) {
      const rawGeneralList = merge2DArrToObjectArr(column, list);
      generalList.value = convertIterableToMap(
        rawGeneralList.map((v) => {
          return { permission, st0: true, st1: false, st2: false, ...v };
        }),
        "no"
      );
    } else if (permission == 1) {
      const rawGeneralList = merge2DArrToObjectArr(column, list);
      generalList.value = convertIterableToMap(
        rawGeneralList.map((v) => {
          return { permission, st0: true, st1: true, st2: false, ...v };
        }),
        "no"
      );
    } else if ([2, 3, 4].includes(permission)) {
      const rawGeneralList = merge2DArrToObjectArr(column, list);
      generalList.value = convertIterableToMap(
        rawGeneralList.map((v) => {
          return { permission, st0: true, st1: true, st2: true, ...v };
        }),
        "no"
      );
    } else {
      throw `?? ${permission}`;
    }

    myPermission.value = permission;

    me.value = unwrap(generalList.value.get(myGeneralID));

    troopList.value = new Map();

    for (const { id: troopLeaderID, name: troopName, turntime: troopTurntime, reservedCommand } of troops) {
      if (!generalList.value.has(troopLeaderID)) {
        toasts.warning({
          title: "경고",
          body: `${troopName} 부대장(${troopLeaderID})이 아국 소속이 아닌 것 같습니다.`,
        });
        continue;
      }
      troopList.value.set(troopLeaderID, {
        troopID: troopLeaderID,
        troopName,
        troopLeader: unwrap(generalList.value.get(troopLeaderID)),
        turnTime: troopTurntime,
        reservedCommandBrief: reservedCommand,
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
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "오류",
        body: e,
      });
    }
    console.error(e);
    return;
  }
}

onMounted(() => {
  void refresh();
});

async function makeTroop() {
  const troopName = newTroopName.value;
  try {
    await SammoAPI.Troop.NewTroop({
      troopName,
    });
    newTroopName.value = "";
    toasts.info({
      title: "완료",
      body: `${troopName} 부대가 생성되었습니다.`,
    });
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "오류",
        body: e,
      });
    }
    console.error(e);
  }
  await refresh();
}

async function joinTroop(troopID: number) {
  try {
    await SammoAPI.Troop.JoinTroop({
      troopID,
    });
    toasts.info({
      title: "완료",
      body: ` ${troopList.value.get(troopID)?.troopName} 부대에 가입했습니다.`,
    });
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "오류",
        body: e,
      });
    }
    console.error(e);
  }
  await refresh();
}

async function exitTroop() {
  const isTroopLeader = me.value.troop == me.value.no;
  const troopName = troopList.value.get(me.value.troop)?.troopName ?? "??";

  if (isTroopLeader) {
    if (!confirm(`${troopName} 부대를 해산하겠습니까?`)) {
      return;
    }
  } else {
    if (!confirm(`${troopName} 부대에서 탈퇴하겠습니까?`)) {
      return;
    }
  }
  try {
    await SammoAPI.Troop.ExitTroop();

    if (isTroopLeader) {
      toasts.info({
        title: "완료",
        body: `부대를 해산했습니다.`,
      });
    } else {
      toasts.info({
        title: "완료",
        body: `부대에서 탈퇴했습니다.`,
      });
    }
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "오류",
        body: e,
      });
    }
    console.error(e);
  }
  await refresh();
}

function openChangeTroopNameDialog(troop: TroopInfo){
  tryTroopActionTarget.value = {
    type: 'changeName',
    targetTroop: troop.troopID
  }
  newTroopName.value = troop.troopName;
}

async function changeTroopName(troop: TroopInfo) {
  const troopID = troop.troopID;
  const troopName = newTroopName.value;
  const oldTroopName = troop.troopName;
  const josaRo = pick(troopName, "로");
  if (!confirm(`${oldTroopName} 부대의 이름을 ${troopName}${josaRo} 바꾸시겠습니까?`)) {
    return;
  }
  try {
    await SammoAPI.Troop.SetTroopName({
      troopID,
      troopName,
    });

    toasts.info({
      title: "완료",
      body: `부대명을 변경했습니다.`,
    });
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "오류",
        body: e,
      });
    }
    console.error(e);
  }
  await refresh();
}


function openKickTroopMemberDialog(troop: TroopInfo){
  tryTroopActionTarget.value = {
    type: 'kickMember',
    targetTroop: troop.troopID
  }
  for(const member of troop.members){
    if(member.no == troop.troopID){
      continue;
    }
    kickTroopMemberID.value = member.no;
    break;
  }
}

async function kickTroopMember(troop: TroopInfo) {
  const kickMember = generalList.value.get(kickTroopMemberID.value);
  if(kickMember === undefined){
    toasts.danger({
      title: "오류",
      body: "잘못된 접근입니다.",
    });
    return;
  }
  const troopID = troop.troopID;
  const troopName = troop.troopName;

  const generalID = kickMember.no;
  const generalName = kickMember.name;
  const josaUl = pick(generalName, "을");
  if (!confirm(`${troopName} 부대에서 ${generalName}${josaUl} 추방하시겠습니까?`)) {
    return;
  }
  try {
    await SammoAPI.Troop.KickFromTroop({
      troopID,
      generalID,
    });

    toasts.info({
      title: "완료",
      body: `${generalName}${josaUl} 추방했습니다.`,
    });
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "오류",
        body: e,
      });
    }
    console.error(e);
  }
  await refresh();
}
</script>

<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";

#container {
  position: relative;
}

#generalPopup {
  position: absolute;
  width: 500px;
}

.additionalTroopOptions {
  margin-top: 1em;
}

.makeNewTroop {
  border: solid 1px gray;
}

.troopDiffCityMemeber{
  color: red;
}

.troopItem {
  display: grid;
  border-right: solid 1px gray;

  > div {
    border-top: solid 1px gray;
    border-left: solid 1px gray;
  }

  .troopInfo {
    grid-column: 1/2;
    grid-row: 1/2;
    display: grid;
    align-content: center;
    text-align: center;
  }

  .troopTurn {
    grid-column: 1/2;
    grid-row: 2/3;
    display: grid;
    align-content: center;
    justify-content: center;
  }

  .troopLeaderIcon {
    grid-column: 2/3;
    grid-row: 1/2;
    display: grid;
    align-content: center;
    justify-content: center;
  }

  .troopLeaderName {
    grid-column: 2/3;
    grid-row: 2/3;
    display: grid;
    align-items: center;
    justify-content: center;
  }

  .troopReservedCommand {
    grid-column: 4/5;
    grid-row: 1/3;
    font-size: 85%;
    text-align: left;
  }

  .troopAction {
    grid-column: 5/6;
    grid-row: 1/3;

    .btn {
      padding-top: 0.2em;
      padding-bottom: 0.2em;
    }
  }

  .troopMembers {
    text-align: left;
    padding: 0.5em 0.7em;
  }
}

@include media-1000px {
  #container {
    width: 1000px;
    margin: 0 auto;
    position: relative;
  }

  #generalPopup {
    left: 260px;
  }

  .troopItem {
    grid-template-rows: 65px 28px;
    grid-template-columns: 130px 130px 1fr 100px 140px;

    .troopMembers {
      grid-column: 3/4;
      grid-row: 1/3;
    }

    &:last-of-type {
      border-bottom: solid 1px gray;
    }
  }
}

@include media-500px {
  #container {
    width: 500px;
    margin: 0 auto;
    position: relative;
  }

  #generalPopup {
    left: 0;
  }

  .troopItem {
    grid-template-rows: 65px 28px auto;
    grid-template-columns: 130px 130px 0px 100px 140px;

    .troopMembers {
      grid-column: 2/6;
      grid-row: 3/4;
    }

    &:last-of-type {
      .troopMembers,
      .troopTurn {
        border-bottom: solid 1px gray;
      }
    }
  }
}
</style>
