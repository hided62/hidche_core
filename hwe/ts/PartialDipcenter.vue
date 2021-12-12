<template>
  <div>국 가 방 침 &amp; 임관 권유 메시지</div>
  <div id="noticeForm">흠</div>
  <!--<b-button>국가방침 수정</b-button><b-button>취소</b-button>-->
  <!--<div id="noticeForm">
    <div class="bg1" style="display: flex; justify-content: space-around">
      <div style="flex: 1 1 auto">국가 방침</div>
      <div>
        <b-button>국가방침 수정</b-button><b-button>취소</b-button>
      </div>
    </div>
    <div class="edit_form viewer"></div>
  </div>

  <div id="scoutMsgForm">
    <div class="bg1" style="display: flex; justify-content: space-around">
      <div style="flex: 1 1 auto">임관 권유</div>
      <div>
        <b-button>임관 권유문 수정</b-button><b-button>취소</b-button>
      </div>
    </div>
    <div style="border-bottom: solid gray 0.5px">
      870px x 200px를 넘어서는 내용은 표시되지 않습니다.
    </div>
    <div style="width: 870px; margin-left: auto">
      <div class="edit_form viewer"></div>
    </div>
  </div>!-->
</template>
<script lang="ts">
import "@scss/dipcenter.scss";
import "@scss/common_legacy.scss";

import { defineComponent } from "vue";

declare const editable: boolean;
declare const nationMsg: string;
declare const scoutMsg: string;

export default defineComponent({
  name: "PartialDipcenter",
  components: {},
  data() {
    console.log(editable, ",", nationMsg, ",", scoutMsg);
    return {
      editable,
      nationMsg,
      scoutMsg,
    };
  },
  /*methods: {},*/
});
</script>