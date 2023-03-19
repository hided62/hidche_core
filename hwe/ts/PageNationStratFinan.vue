<template>
  <BContainer id="container" :toast="{ root: true }" class="pageNationStratFinan bg0">
    <TopBackBar title="내무부" />
    <div class="diplomacyTitle">외교관계</div>
    <div class="diplomacyTable">
      <div class="diplomacyHeader tRow bg1">
        <div>국가명</div>
        <div>국력</div>
        <div>장수</div>
        <div>속령</div>
        <div>상태</div>
        <div>기간</div>
        <div>종료 시점</div>
      </div>
      <div v-for="nation in nationsList" :key="nation.nation" :class="['diplomacyItem', 'tRow', 's-border-b']">
        <div :class="`sam-nation-bg-${nation.color.slice(1)}`">
          {{ nation.name }}
        </div>
        <div>{{ nation.power.toLocaleString() }}</div>
        <div>{{ nation.gennum.toLocaleString() }}</div>
        <div>{{ nation.cityCnt.toLocaleString() }}</div>
        <template v-if="nation.nation == nationID">
          <div>-</div>
        </template>
        <template v-else>
          <div
            :style="{
              color: diplomacyStateInfo[nation.diplomacy.state].color ?? undefined,
            }"
          >
            {{ diplomacyStateInfo[nation.diplomacy.state].name }}
          </div>
          <div>
            {{ nation.diplomacy.term == 0 ? "-" : `${nation.diplomacy.term}개월` }}
          </div>
          <div
            v-for="([endYear, endMonth], _idx) in [
              parseYearMonth(joinYearMonth(year, month) + (nation.diplomacy.term ?? 0)),
            ]"
            :key="_idx"
          >
            {{ nation.diplomacy.term == 0 ? "-" : `${endYear}년 ${endMonth}월` }}
          </div>
        </template>
      </div>
    </div>
    <div class="noticeTitle">국가 방침 &amp; 임관 권유 메시지</div>
    <div id="noticeForm">
      <div class="bg1" style="display: flex; justify-content: space-around">
        <div style="flex: 1 1 auto">국가 방침</div>
        <div>
          <b-button v-if="editable && !inEditNationMsg" @click="enableEditNationMsg"> 국가방침 수정 </b-button>
          <b-button v-if="editable && inEditNationMsg" @click="saveNationMsg"> 저장 </b-button>
          <b-button v-if="editable && inEditNationMsg" @click="rollbackNationMsg"> 취소 </b-button>
        </div>
      </div>
      <TipTap
        v-model="nationMsg"
        :editable="inEditNationMsg"
        @ready="trackNationMsgHeight"
        @update:modelValue="trackNationMsgHeight"
      />
    </div>

    <div id="scoutMsgForm">
      <div class="bg1" style="display: flex; justify-content: space-around">
        <div style="flex: 1 1 auto">임관 권유</div>
        <div>
          <b-button v-if="editable && !inEditScoutMsg" @click="enableEditScoutMsg"> 임관 권유문 수정 </b-button>
          <b-button v-if="editable && inEditScoutMsg" @click="saveScoutMsg"> 저장 </b-button>
          <b-button v-if="editable && inEditScoutMsg" @click="rollbackScoutMsg"> 취소 </b-button>
        </div>
      </div>
      <div style="border-bottom: solid gray 0.5px">870px x 200px를 넘어서는 내용은 표시되지 않습니다.</div>
      <div style="width: 870px; margin-left: auto">
        <TipTap
          v-model="scoutMsg"
          :editable="inEditScoutMsg"
          @ready="trackScoutMsgHeight"
          @update:modelValue="trackScoutMsgHeight"
        />
      </div>
    </div>

    <div class="financeTitle">예산&amp;정책</div>
    <div class="row gx-0 center">
      <div class="col-6">
        <div class="row gx-0">
          <div class="col-12 bg2">자금 예산</div>
          <div class="col-4 bg1">현 재</div>
          <div class="col-8">
            {{ gold.toLocaleString() }}
          </div>
          <div class="col-4 bg1">단기수입</div>
          <div class="col-8">
            {{ income.gold.war.toLocaleString() }}
          </div>
          <div class="col-4 bg1">세 금</div>
          <div class="col-8">
            {{ Math.floor(incomeGoldCity).toLocaleString() }}
          </div>
          <div class="col-4 bg1">수입/지출</div>
          <div class="col-8">
            +{{ Math.floor(incomeGold).toLocaleString() }} /
            {{ Math.floor(-outcomeByBill).toLocaleString() }}
          </div>
          <div class="col-4 bg1">국고 예산</div>
          <div class="col-8">
            {{ Math.floor(gold + incomeGold - outcomeByBill).toLocaleString() }}
            ({{ incomeGold >= outcomeByBill ? "+" : "" }}{{ Math.floor(incomeGold - outcomeByBill).toLocaleString() }})
          </div>
        </div>
      </div>
      <div class="col-6">
        <div class="row gx-0">
          <div class="col-12 bg2">군량 예산</div>
          <div class="col-4 bg1">현 재</div>
          <div class="col-8">
            {{ rice.toLocaleString() }}
          </div>
          <div class="col-4 bg1">둔전수입</div>
          <div class="col-8">
            {{ Math.floor(incomeRiceWall).toLocaleString() }}
          </div>
          <div class="col-4 bg1">세 금</div>
          <div class="col-8">
            {{ Math.floor(incomeRiceCity).toLocaleString() }}
          </div>
          <div class="col-4 bg1">수입/지출</div>
          <div class="col-8">
            +{{ Math.floor(incomeRice).toLocaleString() }} /
            {{ Math.floor(-outcomeByBill).toLocaleString() }}
          </div>
          <div class="col-4 bg1">국고 예산</div>
          <div class="col-8">
            {{ Math.floor(rice + incomeRice - outcomeByBill).toLocaleString() }}
            ({{ incomeRice >= outcomeByBill ? "+" : "" }}{{ Math.floor(incomeRice - outcomeByBill).toLocaleString() }})
          </div>
        </div>
      </div>
      <div class="col-6">
        <div class="row gx-0">
          <div class="col-4 bg1 d-grid">
            <div class="align-self-center">세율 <span class="avoid-wrap">(5 ~ 30%)</span></div>
          </div>
          <div class="col-8 row gx-0">
            <div class="col-md-6 offset-md-3 align-self-center">
              <div class="input-group my-0">
                <input
                  v-model="policy.rate"
                  type="number"
                  class="form-control py-1 f_tnum px-0 text-end"
                  min="5"
                  max="30"
                /><span class="input-group-text py-1 f_tnum">%</span
                ><b-button v-if="editable" variant="primary" size="sm" @click="setRate"> 변경 </b-button
                ><b-button v-if="editable" size="sm" @click="rollbackRate"> 취소 </b-button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6">
        <div class="row gx-0">
          <div class="col-4 bg1 d-grid">
            <div class="align-self-center">지급률 <span class="avoid-wrap">(20 ~ 200%)</span></div>
          </div>
          <div class="col-8 row gx-0">
            <div class="col-md-6 offset-md-3 align-self-center">
              <div class="input-group my-0">
                <input
                  v-model="policy.bill"
                  type="number"
                  class="form-control py-1 f_tnum px-0 text-end"
                  min="20"
                  max="200"
                /><span class="input-group-text py-1 f_tnum">%</span
                ><b-button v-if="editable" variant="primary" size="sm" @click="setBill"> 변경 </b-button
                ><b-button v-if="editable" size="sm" @click="rollbackBill"> 취소 </b-button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6">
        <div class="row gx-0">
          <div class="col-4 bg1 d-grid">
            <div class="align-self-center">기밀 권한 <span class="avoid-wrap">(1 ~ 99년)</span></div>
          </div>
          <div class="col-8 row gx-0">
            <div class="col-md-6 offset-md-3 align-self-center">
              <div class="input-group my-0">
                <input
                  v-model="policy.secretLimit"
                  type="number"
                  class="form-control py-1 f_tnum px-0 text-end"
                  min="1"
                  max="99"
                /><span class="input-group-text py-1 f_tnum">년</span
                ><b-button v-if="editable" variant="primary" size="sm" @click="setSecretLimit"> 변경 </b-button
                ><b-button v-if="editable" size="sm" @click="rollbackSecretLimit"> 취소 </b-button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 d-grid">
        <div class="row gx-0">
          <div class="col-4 bg1 d-grid">
            <div class="align-self-center">전쟁 금지 설정</div>
          </div>
          <div class="col-8 d-grid">
            <div class="align-self-center">
              {{ warSettingCnt.remain }} 회(월 +{{ warSettingCnt.inc }}회, 최대{{ warSettingCnt.max }}회)
            </div>
          </div>
        </div>
      </div>
      <div class="col-3 col-md-4" />
      <div class="col-3 col-md-2 row gx-0">
        <div class="col-9 col-md-8 text-end p-2">전쟁 금지</div>
        <div class="col-3 col-md-4 py-2">
          <b-form-checkbox v-model="policy.blockWar" switch @change="setBlockWar" />
        </div>
      </div>
      <div class="col-3 col-md-2 row gx-0">
        <div class="col-9 col-md-8 text-end p-2">임관 금지</div>
        <div class="col-3 col-md-4 py-2">
          <b-form-checkbox v-model="policy.blockScout" switch @change="setBlockScout" />
        </div>
      </div>
    </div>
    <div>추가 설정</div>
    <BottomBar title="내무부" />
  </BContainer>
</template>

<script lang="ts">
type NationItem = NationStaticItem & {
  cityCnt: number;
  diplomacy: {
    state: diplomacyState;
    term: number | null;
  };
};

declare const staticValues: {
  editable: boolean;
  nationMsg: string;
  scoutMsg: string;
  nationID: number;
  officerLevel: number;
  year: number;
  month: number;
  nationsList: Record<number, NationItem>;

  gold: number;
  rice: number;
  income: {
    gold: {
      city: number;
      war: number;
    };
    rice: {
      city: number;
      wall: number;
    };
  };
  outcome: number;

  policy: {
    rate: number;
    bill: number;
    secretLimit: number;
    blockScout: boolean;
    blockWar: boolean;
  };
  warSettingCnt: {
    remain: number;
    inc: number;
    max: number;
  };
};
</script>
<script setup lang="ts">
import TipTap from "./components/TipTap.vue";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { computed, reactive, ref, toRefs } from "vue";
import { isString } from "lodash-es";
import { type diplomacyState, diplomacyStateInfo, type NationStaticItem } from "./defs";
import { SammoAPI } from "./SammoAPI";
import { joinYearMonth } from "@/util/joinYearMonth";
import { parseYearMonth } from "@/util/parseYearMonth";
import { useToast, BContainer } from "bootstrap-vue-next";
import { unwrap } from "./util/unwrap";

const toasts = unwrap(useToast());
const self = reactive(staticValues);
const {
  editable,
  nationMsg,
  scoutMsg,
  nationID,
  year,
  month,
  nationsList,
  gold,
  rice,
  income,
  policy,
  warSettingCnt,
} = toRefs(self);

let oldNationMsg = staticValues.nationMsg;
const inEditNationMsg = ref(false);

function enableEditNationMsg() {
  inEditNationMsg.value = true;
}

function rollbackNationMsg() {
  inEditNationMsg.value = false;
  self.nationMsg = oldNationMsg;
}

async function saveNationMsg() {
  const msg = self.nationMsg;
  try {
    await SammoAPI.Nation.SetNotice({
      msg,
    });
    oldNationMsg = msg;
    inEditNationMsg.value = false;
    toasts.info({
      title: "변경",
      body: "국가 방침을 변경했습니다.",
    });
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
    console.error(e);
  }
}

let oldScoutMsg = staticValues.scoutMsg;
const inEditScoutMsg = ref(false);

function enableEditScoutMsg() {
  inEditScoutMsg.value = true;
}
function rollbackScoutMsg() {
  inEditScoutMsg.value = false;
  self.scoutMsg = oldScoutMsg;
}
async function saveScoutMsg() {
  const msg = self.scoutMsg;
  try {
    await SammoAPI.Nation.SetScoutMsg({
      msg,
    });
    oldScoutMsg = msg;
    inEditScoutMsg.value = false;
    toasts.info({
      title: "변경",
      body: "임관 권유문을 변경했습니다.",
    });
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
    console.error(e);
  }
}

const trackTiptapFormHeight = (target: string) => {
  let form: HTMLElement | null = null;
  let outerForm: HTMLElement | null = null;
  function handler() {
    if (!form) {
      form = document.querySelector(`${target} .ProseMirror`);
    }
    if (!outerForm) {
      outerForm = document.querySelector(`${target} .tiptap-editor`);
    }
    if (!form || !outerForm) {
      return;
    }

    const { height: clientHeight } = form.getBoundingClientRect();
    const { height: parentHeight } = outerForm.getBoundingClientRect();

    if (parentHeight != clientHeight) {
      outerForm.style.height = `${clientHeight}px`;
    }
  }
  window.addEventListener("orientationchange", handler, true);

  return handler;
};
const trackNationMsgHeight = trackTiptapFormHeight("#noticeForm");
const trackScoutMsgHeight = trackTiptapFormHeight("#scoutMsgForm");

const incomeGoldCity = computed(() => {
  return (self.income.gold.city * self.policy.rate) / 100;
});

const incomeGold = computed(() => {
  return incomeGoldCity.value + self.income.gold.war;
});

const incomeRiceCity = computed(() => {
  return (self.income.rice.city * self.policy.rate) / 100;
});

const incomeRiceWall = computed(() => {
  return (self.income.rice.wall * self.policy.rate) / 100;
});

const incomeRice = computed(() => {
  return incomeRiceCity.value + incomeRiceWall.value;
});

const outcomeByBill = computed(() => {
  return (self.outcome * self.policy.bill) / 100;
});

let oldRate = staticValues.policy.rate;
async function setRate() {
  const rate = self.policy.rate;
  try {
    await SammoAPI.Nation.SetRate({ amount: rate });
    oldRate = rate;
    toasts.info({
      title: "변경",
      body: "세율을 변경했습니다.",
    });
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
    console.error(e);
  }
}
function rollbackRate() {
  self.policy.rate = oldRate;
}

let oldBill = staticValues.policy.bill;
async function setBill() {
  const bill = self.policy.bill;
  try {
    await SammoAPI.Nation.SetBill({ amount: bill });
    oldBill = bill;
    toasts.info({
      title: "변경",
      body: "지급률을 변경했습니다.",
    });
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
    console.error(e);
  }
}
function rollbackBill() {
  self.policy.bill = oldBill;
}

let oldSecretLimit = staticValues.policy.secretLimit;
async function setSecretLimit() {
  const secretLimit = self.policy.secretLimit;
  try {
    await SammoAPI.Nation.SetSecretLimit({ amount: secretLimit });
    oldSecretLimit = secretLimit;
    toasts.info({
      title: "변경",
      body: "기밀 권한을 변경했습니다.",
    });
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
    self.policy.secretLimit = oldSecretLimit;
    console.error(e);
  }
}
function rollbackSecretLimit() {
  self.policy.secretLimit = oldSecretLimit;
}

async function setBlockWar() {
  try {
    const result = await SammoAPI.Nation.SetBlockWar({ value: self.policy.blockWar });
    self.warSettingCnt.remain = result.availableCnt;
    toasts.info({
      title: "변경",
      body: "전쟁 금지 설정을 변경했습니다.",
    });
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
    self.policy.blockWar = !self.policy.blockWar;
    console.error(e);
  }
}

async function setBlockScout() {
  try {
    await SammoAPI.Nation.SetBlockScout({ value: self.policy.blockScout });
    toasts.info({
      title: "변경",
      body: "임관 설정을 변경했습니다.",
    });
  } catch (e) {
    if (isString(e)) {
      toasts.danger({
        title: "에러",
        body: e,
      });
    }
    self.policy.blockScout = !self.policy.blockScout;
    console.error(e);
  }
}

</script>
