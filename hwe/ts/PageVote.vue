<template>
  <BContainer id="container" :toast="{ root: true }" class="pageVote bg0">
    <TopBackBar :reloadable="true" title="" @reload="reloadVote" />
    <div id="vote-title" class="bg2">설문 조사({{ voteReward }}금과 추첨으로 유니크템 증정!</div>
    <table v-if="currentVote" id="vote-result">
      <colgroup>
        <col class="vote-idx" />
        <col class="vote-count" />
        <col class="vote-percent" />
        <col class="vote-option" />
      </colgroup>
      <thead>
        <tr>
          <th colspan="3" class="text-end bg1">설문 제목</th>
          <th id="vote-detail-title">{{ currentVote.voteInfo.title }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(option, idx) in currentVote.voteInfo.options" :key="idx">
          <template v-if="canVote">
            <td v-if="currentVote.voteInfo.multipleOptions == 1" class="text-center">
              <input :id="`v-vote-${idx}`" v-model="mySinglePick" class="form-check-input" type="radio" :value="idx" />
            </td>
            <td v-else class="text-center">
              <input
                :id="`v-vote-${idx}`"
                v-model="myMultiPick"
                class="form-check-input"
                type="checkbox"
                :value="idx"
              />
            </td>
          </template>
          <td
            v-else
            class="text-end f_tnum"
            :style="{
              backgroundColor: formatVoteColor(idx),
              color: isBrightColor(formatVoteColor(idx)) ? '#000' : '#fff',
            }"
          >
            {{ idx + 1 }}.
          </td>

          <td class="text-end f_tnum vote-count">
            <label :for="`v-vote-${idx}`">{{ voteDistribution[idx] }}명</label>
          </td>
          <td class="text-end f_tnum vote-percent">
            <label :for="`v-vote-${idx}`"
              >({{ ((voteDistribution[idx] / Math.max(1, voteTotal)) * 100).toFixed(1) }}%)</label
            >
          </td>
          <td>
            <label :for="`v-vote-${idx}`">{{ option }}</label>
          </td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <template v-if="canVote">
            <td class="text-center">투표</td>
            <td colspan="2">
              <div class="d-grid"><BButton @click="submitVote">투표</BButton></div>
            </td>
          </template>
          <td v-else colspan="3" class="text-center">결산</td>
          <td>
            투표율: {{ voteTotal }} / {{ currentVote.userCnt }} (
            {{ ((voteTotal / Math.max(1, currentVote.userCnt)) * 100).toFixed(1) }}%)
          </td>
          <td></td>
        </tr>
      </tfoot>
    </table>
    <form @submit.prevent="submitComment">
      <table v-if="currentVote" id="vote-comment">
        <colgroup>
          <col class="comment-idx" />
          <col class="comment-name" />
          <col class="comment-text" />
          <col class="comment-date" />
        </colgroup>
        <thead>
          <tr class="bg1 text-center">
            <th>#</th>
            <th>
              <div class="row gx-0">
                <div class="col-12 col-md-6">국가명</div>
                <div class="col-12 col-md-6">장수명</div>
              </div>
            </th>
            <th>댓글</th>
            <th>일시</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(comment, idx) in currentVote.comments" :key="idx">
            <td class="comment-idx f_tnum">{{ idx + 1 }}.</td>
            <td class="comment-name">
              <div class="row gx-0">
                <div class="col-12 col-md-6">{{ comment.nationName }}</div>
                <div class="col-12 col-md-6">{{ comment.generalName }}</div>
              </div>
            </td>
            <td>{{ comment.text }}</td>
            <td class="comment-date f_tnum">{{ comment.date.substring(5, 5 + 5 + 1 + 5) }}</td>
          </tr>
        </tbody>

        <tfoot>
          <tr>
            <td></td>
            <td>
              <div class="offset-md-6 d-grid"><BButton type="submit">댓글 달기</BButton></div>
            </td>
            <td colspan="2"><BFormInput v-model="myComment" /></td>
          </tr>
        </tfoot>
      </table>
    </form>
    <div id="vote-old-title" class="bg2">이전 설문 조사</div>
    <div id="vote-old-list">
      <div v-for="[voteID, voteInfo] of voteList" :key="voteID" class="vote-old-item">
        <div class="row">
          <div class="col"><a href="#" @click.prevent="currentVoteID = voteID">{{ voteInfo.title }}</a> ({{ voteInfo.startDate }})</div>
        </div>
      </div>
    </div>
    <div v-if="isVoteAdmin" id="vote-new-panel">
      <div class="row"><a href="#" @click.prevent="showNewVote = !showNewVote">새 설문 조사 열기</a></div>
      <template v-if="showNewVote">
        <div class="row gx-0">
          <div class="col-md-3">설문 제목</div>
          <div class="col-md-9"><BFormInput v-model="newVoteInfo.title" type="text" /></div>
        </div>
        <div class="row gx-0">
          <div class="col-md-3">설문 대상(엔터로 구분) ({{ newVoteInfo.options.length }}건)</div>
          <div class="col-md-9">
            <textarea v-model="newVoteOptionsText" class="form-control" :rows="newVoteOptionsLength + 1"></textarea>
          </div>
        </div>
        <div class="row gx-0">
          <div class="col-md-3">동시 응답 수(0=모두)</div>
          <div class="col-md-9">
            <BFormInput
              v-model="newVoteInfo.multipleOptions"
              type="number"
              :min="0"
              :max="newVoteInfo.options.length"
            />
          </div>
        </div>
        <div class="row gx-0">
          <div class="offset-8 col-4 offset-md-10 col-md-2 d-grid">
            <BButton @click="submitNewVote">제출</BButton>
          </div>
        </div>
      </template>
    </div>
    <BottomBar type="close" />
  </BContainer>
</template>

<script lang="ts">
declare const staticValues: {
  serverNick: string;
  serverID: string;
  isGameLoggedIn: boolean;
  isVoteAdmin: boolean;
};
</script>
<script lang="ts" setup>
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { BContainer, useToast, BButton, BFormInput } from "bootstrap-vue-3";
import { unwrap } from "@/util/unwrap";
import { onMounted, reactive, ref, watch, computed } from "vue";
import type { VoteInfo, VoteDetailResult } from "@/defs/API/Vote";
import { SammoAPI } from "@/SammoAPI";
import { isString, range, sum } from "lodash";
import { formatTime } from "@/util/formatTime";
import { isBrightColor } from "@/util/isBrightColor";
import { formatVoteColor } from "@/utilGame/formatVoteColor";

const { isVoteAdmin, isGameLoggedIn } = staticValues;
const voteList = ref(new Map<number, VoteInfo>());

const voteReward = 10;
const toasts = unwrap(useToast());

const showNewVote = ref(false);

const voteTotal = ref(0);
const voteDistribution = ref<Record<number, number>>({});

const currentVote = ref<VoteDetailResult>();
watch(currentVote, (voteResult) => {
  if (!voteResult) {
    return;
  }
  const voteDist: Record<number, number> = {};
  for (const idx of range(voteResult.voteInfo.options.length)) {
    voteDist[idx] = 0;
  }
  voteTotal.value = sum(voteResult.votes.map(([, count]) => count));

  for (const [selection, count] of voteResult.votes) {
    for (const idx of selection) {
      voteDist[idx] += count;
    }
  }

  voteDistribution.value = voteDist;
});

const canVote = computed(() => {
  if (!isGameLoggedIn) {
    return false;
  }
  if (!currentVote.value) {
    return false;
  }
  if (currentVote.value.myVote) {
    return false;
  }
  const endDate = currentVote.value.voteInfo.endDate;
  if (endDate) {
    const now = formatTime(new Date());
    if (now > endDate) {
      return false;
    }
  }
  return true;
});

const currentVoteID = ref<number>();
watch(currentVoteID, async (voteID) => {
  if (voteID === undefined) {
    return;
  }
  await reloadVoteDetail(voteID);
});

const mySinglePick = ref(0);
const myMultiPick = ref<number[]>([]);

async function submitVote() {
  if (currentVote.value === undefined) {
    return;
  }
  const selection = currentVote.value.voteInfo.multipleOptions !== 1 ? myMultiPick.value : [mySinglePick.value];

  if (selection.length === 0) {
    toasts.danger({
      title: "오류",
      body: "선택한 항목이 없습니다.",
    });
    return;
  }

  const voteID = currentVote.value.voteInfo.id;

  try {
    const result = await SammoAPI.Vote.Vote({
      voteID,
      selection,
    });

    toasts.success({
      title: "성공",
      body: "설문을 마쳤습니다.",
    });
    if (result.wonLottery) {
      toasts.info({
        title: "성공",
        body: "특별한 설문 보상이 제공되었습니다!",
      });
    }
    void reloadVoteDetail(voteID);
  } catch (e) {
    console.error(e);
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
  }
}

const myComment = ref("");
async function submitComment() {
  if (currentVote.value === undefined) {
    return;
  }

  const voteID = currentVote.value.voteInfo.id;
  const text = myComment.value;

  try {
    await SammoAPI.Vote.AddComment({
      voteID,
      text,
    });

    toasts.success({
      title: "성공",
      body: "댓글을 달았습니다.",
    });
    myComment.value = "";
    void reloadVoteDetail(voteID);
  } catch (e) {
    console.error(e);
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
  }
}

const newVoteInfo = reactive<Omit<VoteInfo, "id" | "startDate">>({
  title: "",
  multipleOptions: 1,
  endDate: undefined,
  options: [],
});

const newVoteOptionsLength = ref(0);
const newVoteOptionsText = ref("");

watch(newVoteOptionsText, (newValue) => {
  const lines = newValue.split("\n");
  newVoteInfo.options = lines.filter((v) => v.length > 0);
  newVoteOptionsLength.value = lines.length;
});

async function reloadVoteDetail(voteID: number) {
  try {
    const result = await SammoAPI.Vote.GetVoteDetail({ voteID });
    currentVote.value = result;

    if (voteID !== currentVoteID.value) {
      myMultiPick.value = [];
      mySinglePick.value = 0;
      myComment.value = "";
    }
  } catch (e) {
    console.error(e);
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
  }
}

async function reloadVote() {
  try {
    const result = await SammoAPI.Vote.GetVoteList();
    const newVoteList = new Map<number, VoteInfo>();
    for (const [rawVoteID, info] of Object.entries(result.votes).reverse()) {
      newVoteList.set(parseInt(rawVoteID), info);
    }
    voteList.value = newVoteList;
    if (newVoteList.size > 0 && currentVoteID.value === undefined) {
      currentVoteID.value = newVoteList.keys().next().value;
    } else if (currentVoteID.value !== undefined) {
      void reloadVoteDetail(currentVoteID.value);
    }
  } catch (e) {
    console.error(e);
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
  }
}

onMounted(function () {
  void reloadVote();
});

async function submitNewVote() {
  try {
    await SammoAPI.Vote.NewVote(newVoteInfo);
    toasts.success({
      title: "성공",
      body: "설문 조사가 생성되었습니다.",
    });
    void reloadVote();
  } catch (e) {
    console.error(e);
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
  }
}
</script>

<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";

#vote-title {
  font-size: 2em;
  text-align: center;
}

#vote-old-title {
  font-size: 1.5em;
  text-align: center;
}

#vote-result {
  width: 100%;

  th,
  td {
    padding-left: 1ch;
    padding-right: 1ch;
  }

  label {
    display: block;
  }

  .vote-idx {
    width: 5ch;
  }

  .vote-count {
    width: 55px;
    padding-right: 0;
  }

  .vote-percent {
    width: 70px;
    padding-left: 0;
  }
}

#vote-comment {
  width: 100%;

  th,
  td {
    padding-left: 1ch;
    padding-right: 1ch;
  }

  .comment-idx {
    width: 5ch;
    text-align: end;
  }

  .comment-name {
    width: 110px;
    text-align: center;
  }

  tbody tr {
    border-top: solid gray 1px;
  }
}

@include media-1000px {
  #vote-comment {
    .comment-name {
      width: 260px;
    }

    .comment-date {
      width: 98px;
      padding-left: 0.5ch;
      padding-right: 0.5ch;
      text-align: center;
    }
  }
}

@include media-500px {
  #vote-comment {
    .comment-name {
      width: 130px;
    }

    .comment-date {
      width: 50px;
      padding-left: 0.5ch;
      padding-right: 0.5ch;
      text-align: center;
    }
  }
}
</style>
