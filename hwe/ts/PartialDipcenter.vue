<template>
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
      <TipTap v-model="scoutMsg" :editable="inEditScoutMsg" />
    </div>
  </div>
</template>
<script lang="ts">
import "@scss/dipcenter.scss";
import "@scss/common_legacy.scss";
import TipTap from "./components/TipTap.vue";
import { defineComponent } from "vue";
import { sammoAPI } from "./util/sammoAPI";
import { isString } from "lodash";
declare const editable: boolean;
declare const nationMsg: string;
declare const scoutMsg: string;

export default defineComponent({
  name: "PartialDipcenter",
  components: {
    TipTap,
  },
  data() {
    return {
      editable,
      oldNationMsg: nationMsg,
      oldScoutMsg: scoutMsg,
      nationMsg,
      scoutMsg,
      inEditNationMsg: false,
      inEditScoutMsg: false,
    };
  },
  methods: {
    enableEditNationMsg() {
      this.inEditNationMsg = true;
    },
    rollbackNationMsg() {
      this.inEditNationMsg = false;
      this.nationMsg = this.oldNationMsg;
    },
    async saveNationMsg() {
      const msg = this.nationMsg;
      try {
        await sammoAPI("Nation/SetNotice", {
          msg,
        });
        this.oldNationMsg = msg;
        this.inEditNationMsg = false;
      } catch (e) {
        if (isString(e)) {
          alert(e);
        }
        console.error(e);
      }
    },

    enableEditScoutMsg() {
      this.inEditScoutMsg = true;
    },
    rollbackScoutMsg() {
      this.inEditScoutMsg = false;
      this.scoutMsg = this.oldScoutMsg;
    },
    async saveScoutMsg() {
      const msg = this.scoutMsg;
      try {
        await sammoAPI("Nation/SetScoutMsg", {
          msg,
        });
        this.oldScoutMsg = msg;
        this.inEditScoutMsg = false;
      } catch (e) {
        if (isString(e)) {
          alert(e);
        }
        console.error(e);
      }
    },
  },
});
</script>