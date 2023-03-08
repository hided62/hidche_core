<template>
  <h3 class="scenarioName center">
    {{ gameConstStore?.gameConst.title }} {{ serverName }}{{ frontInfo?.global.serverCnt }}기
    <span class="avoid-wrap" style="color: cyan">{{ frontInfo?.global.scenarioText }}</span>
  </h3>
  <div v-if="frontInfo" class="gameInfo row gx-0">
    <div class="s-border-t col py-2 col-8 col-md-4 subScenarioName" style="color: cyan">
      {{ globalInfo.scenarioText }}
    </div>
    <div class="s-border-t col py-2 col-4 col-md-2 subNPCType" style="color: cyan">
      NPC 수, 상성:
      {{ globalInfo.extendedGeneral ? "확장" : "표준" }}
      {{ globalInfo.isFiction ? "가상" : "사실" }}
    </div>
    <div class="s-border-t col py-2 col-4 col-md-2 subNPCMode" style="color: cyan">
      NPC선택: {{ ["불가능", "가능", "선택 생성"][globalInfo.npcMode] }}
    </div>
    <div class="s-border-t col py-2 col-4 col-md-2 subTournamentMode" style="color: cyan">
      토너먼트: 경기당 {{ calcTournamentTerm(globalInfo.turnterm) }}분
    </div>
    <div class="s-border-t col py-2 col-4 col-md-2 subOtherSetting" style="color: cyan">
      기타 설정:
      <AutorunInfo :autorunMode="globalInfo.autorunUser" />
    </div>
    <div class="s-border-t col py-2 col-8 col-md-4 subYearMonth">
      현재: {{ globalInfo.year }}年 {{ globalInfo.month }}月 ({{ globalInfo.turnterm }}분 턴 서버)
    </div>
    <div class="s-border-t col py-2 col-4 col-md-2 subOnlineUserCnt">
      전체 접속자 수: {{ globalInfo.onlineUserCnt.toLocaleString() }}명
    </div>
    <div class="s-border-t col py-2 col-4 col-md-2 subAPILimit">
      턴당 갱신횟수: {{ globalInfo.apiLimit.toLocaleString() }}회
    </div>
    <div class="s-border-t col py-2 col-8 col-md-4 subGeneralCnt">
      등록 장수: 유저
      {{ createdUserCnt.toLocaleString() }} / {{ globalInfo.generalCntLimit.toLocaleString() }} +
      <span style="color: cyan">NPC {{ createdNPCCnt.toLocaleString() }} 명</span>
    </div>
    <div class="s-border-t py-2 col col-6 col-md-4 subTournamentState">
      <span v-if="frontInfo.global.tournamentType">
        <a v-if="tournamentStep.availableJoin" href="b_tournament.php" target="_blank">
          ↑<span style="color: cyan"
            >{{ formatTournamentType(frontInfo.global.tournamentType) }}
            <span style="color: orange">{{ tournamentStep.state }}</span> {{ tournamentStep.nextText }}
            {{ formatTime(tournamentTime).substring(11, 16) }}</span
          >↑
        </a>
        <span v-else>
          ↑<span style="color: cyan"
            >{{ formatTournamentType(frontInfo.global.tournamentType) }}
            <span style="color: magenta">{{ tournamentStep.state }}</span> {{ tournamentStep.nextText }}
            {{ formatTime(tournamentTime).substring(11, 16) }}</span
          >↑
        </span>
      </span>
      <span v-else style="color: magenta"> 현재 토너먼트 경기 없음 </span>
    </div>
    <div
      class="s-border-t py-2 col col-6 col-md-2 subLastExecuted"
      :style="{ color: serverLocked ? 'magenta' : 'cyan' }"
    >
      동작 시각: {{ formatTime(lastExecuted).substring(5) }}
    </div>
    <div class="s-border-t py-2 col col-6 col-md-2 subAuctionState">
      <a v-if="globalInfo.auctionCount" href="v_auction.php" target="_blank" style="color: cyan">
        {{ globalInfo.auctionCount.toLocaleString() }}건 거래 진행중
      </a>
      <span v-else style="color: magenta">진행중인 거래 없음</span>
    </div>
    <div class="s-border-t py-2 col col-6 col-md-4 subVoteState">
      <a v-if="globalInfo.lastVote" href="v_vote.php" target="_blank">
        <span style="color: cyan">설문 진행 중: </span><span>{{ globalInfo.lastVote.title }}</span>
      </a>
      <span v-else style="color: magenta">진행중인 설문 없음</span>
    </div>
  </div>
</template>
<script setup lang="ts">
import type { GetFrontInfoResponse } from "@/defs/API/Global";
import type { GameConstStore } from "@/GameConstStore";
import { unwrap } from "@/util/unwrap";
import { inject, toRefs, ref, watch } from "vue";
import { formatTime } from "@/util/formatTime";
import { calcTournamentTerm } from "@/utilGame";
import { formatTournamentStep, type TournamentStepType, formatTournamentType } from "@/utilGame/formatTournament";
import { parseTime } from "@/util/parseTime";
import AutorunInfo from "./AutorunInfo.vue";

const props = defineProps<{
  frontInfo: GetFrontInfoResponse;
  serverName: string;
  serverLocked: boolean;
  lastExecuted: Date;
}>();

const { frontInfo, serverName, serverLocked, lastExecuted } = toRefs(props);

const globalInfo = ref(frontInfo.value.global);

const gameConstStore = unwrap(inject<GameConstStore>("gameConstStore"));

const generalCnt = ref(new Map<number, number>());
const createdUserCnt = ref(0);
const createdNPCCnt = ref(0);

const tournamentStep = ref<TournamentStepType>({
  availableJoin: false,
  state: "초기화 중",
  nextText: "",
});
const tournamentTime = ref<Date>(new Date());

function updateFrontInfo(frontInfo: GetFrontInfoResponse){
  const global = frontInfo.global;
  globalInfo.value = global;

  const value = new Map<number, number>();
  let userCnt = 0;
  let npcCnt = 0;
  for (const [npcType, cnt] of global.genCount) {
    value.set(npcType, cnt);
    if (npcType < 2) {
      userCnt += cnt;
    } else {
      npcCnt += cnt;
    }
  }
  generalCnt.value = value;
  createdUserCnt.value = userCnt;
  createdNPCCnt.value = npcCnt;

  tournamentStep.value = formatTournamentStep(global.tournamentState);
  tournamentTime.value = parseTime(global.tournamentTime);
}

watch(frontInfo, updateFrontInfo, {immediate: true});

</script>

<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";

.gameInfo {
  text-align: center;
}

.subVoteState,
.subAuctionState,
.subTournamentState {
  a {
    text-decoration: gray underline;
  }
}

</style>
