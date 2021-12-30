<template>
  <div id="container" class="pageNationStratFinan bg0">
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
      <div
        :class="['diplomacyItem', 'tRow', 's-border-b']"
        v-for="nation in nationsList"
        :key="nation.nation"
      >
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
              color:
                diplomacyStateInfo[nation.diplomacy.state].color ?? undefined,
            }"
          >
            {{ diplomacyStateInfo[nation.diplomacy.state].name }}
          </div>
          <div>
            {{
              nation.diplomacy.term == 0 ? "-" : `${nation.diplomacy.term}개월`
            }}
          </div>
          <div
            v-for="([endYear, endMonth], _idx) in [
              parseYearMonth(
                joinYearMonth(year, month) + nation.diplomacy.term
              ),
            ]"
            :key="_idx"
          >
            {{
              nation.diplomacy.term == 0 ? "-" : `${endYear}년 ${endMonth}월`
            }}
          </div>
        </template>
      </div>
    </div>
    <div class="noticeTitle">국가 방침 &amp; 임관 권유 메시지</div>
    <div id="noticeForm">
      <div class="bg1" style="display: flex; justify-content: space-around">
        <div style="flex: 1 1 auto">국가 방침</div>
        <div>
          <b-button
            @click="enableEditNationMsg"
            v-if="editable && !inEditNationMsg"
            >국가방침 수정</b-button
          >
          <b-button @click="saveNationMsg" v-if="editable && inEditNationMsg"
            >저장</b-button
          >
          <b-button
            v-if="editable && inEditNationMsg"
            @click="rollbackNationMsg"
            >취소</b-button
          >
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
          <b-button
            @click="enableEditScoutMsg"
            v-if="editable && !inEditScoutMsg"
            >임관 권유문 수정</b-button
          >
          <b-button @click="saveScoutMsg" v-if="editable && inEditScoutMsg"
            >저장</b-button
          >
          <b-button v-if="editable && inEditScoutMsg" @click="rollbackScoutMsg"
            >취소</b-button
          >
        </div>
      </div>
      <div style="border-bottom: solid gray 0.5px">
        870px x 200px를 넘어서는 내용은 표시되지 않습니다.
      </div>
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
    <div>추가 설정</div>
    <BottomBar title="내무부" />
  </div>
</template>
<script lang="ts">
import TipTap from "./components/TipTap.vue";
import { defineComponent, reactive, ref, toRefs } from "vue";
import { isString } from "lodash";
import { diplomacyState, diplomacyStateInfo, NationStaticItem } from "./defs";
import { SammoAPI } from "./SammoAPI";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import { joinYearMonth } from "@/util/joinYearMonth";
import { parseYearMonth } from "@/util/parseYearMonth";

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

  polcy: {
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

export default defineComponent({
  components: {
    TipTap,
    TopBackBar,
    BottomBar,
  },
  setup() {
    staticValues;
    const self = reactive(staticValues);

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
      } catch (e) {
        if (isString(e)) {
          alert(e);
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
      } catch (e) {
        if (isString(e)) {
          alert(e);
        }
        console.error(e);
      }
    }

    const trackTiptapFormHeight = (target: string) => {
      let form: HTMLElement | null = null;
      let outerForm: HTMLElement | null = null;
      return () => {
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
      };
    };

    return {
      ...toRefs(self),
      inEditNationMsg,
      inEditScoutMsg,
      enableEditNationMsg,
      rollbackNationMsg,
      saveNationMsg,
      enableEditScoutMsg,
      rollbackScoutMsg,
      saveScoutMsg,
      diplomacyStateInfo,
      joinYearMonth,
      parseYearMonth,

      trackNationMsgHeight: trackTiptapFormHeight("#noticeForm"),
      trackScoutMsgHeight: trackTiptapFormHeight("#scoutMsgForm"),
    };
  },
  methods: {},
});
</script>

<style lang="scss">
@import "@scss/nationStratFinan.scss";
</style>