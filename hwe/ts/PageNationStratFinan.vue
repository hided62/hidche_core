<template>
  <div>위쪽버튼</div>
  <div>외교관계</div>

  <div>국 가 방 침 &amp; 임관 권유 메시지</div>
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
        <b-button v-if="editable && inEditNationMsg" @click="rollbackNationMsg"
          >취소</b-button
        >
      </div>
    </div>
    <TipTap v-model="nationMsg" :editable="inEditNationMsg" />
  </div>

  <div id="scoutMsgForm">
    <div class="bg1" style="display: flex; justify-content: space-around">
      <div style="flex: 1 1 auto">임관 권유</div>
      <div>
        <b-button @click="enableEditScoutMsg" v-if="editable && !inEditScoutMsg"
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
      <TipTap v-model="scoutMsg" :editable="inEditScoutMsg" />
    </div>
  </div>

  <div>예산&amp;정책</div>
  <div>추가 설정</div>
  <div>돌아가기</div>
</template>
<script lang="ts">
import "@scss/dipcenter.scss";
import "@scss/common_legacy.scss";
import "@scss/editor_component.scss";
import TipTap from "./components/TipTap.vue";
import { defineComponent, reactive, ref, toRefs } from "vue";
import { sammoAPI } from "./util/sammoAPI";
import { isString } from "lodash";
import { diplomacyState, NationStaticItem } from "./defs";

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
  },
  setup() {
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
        await sammoAPI("Nation/SetNotice", {
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
        await sammoAPI("Nation/SetScoutMsg", {
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
    };
  },
  methods: {},
});
</script>