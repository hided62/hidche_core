<template>
  <table width="300" class="tb_layout b2">
    <thead>
      <tr height="24">
        <td colspan="4" class="center bg0">
          <strong>- 명령 목록 - </strong
          ><input
            :value="serverNow"
            type="text"
            id="clock"
            size="19"
            style="background-color: black; color: white; border-style: none"
          />
        </td>
      </tr>
      <tr>
        <td colspan="4">
          <div class="row gx-1">
            <div class="col d-grid">
              <b-dropdown right split text="당기기">
                <b-dropdown-item v-for="turnIdx in maxPushTurn" :key="turnIdx"
                  >{{ turnIdx }}턴
                </b-dropdown-item>
              </b-dropdown>
            </div>
            <div class="col d-grid">
              <b-dropdown right split @click="selectAll" text="전체선택">
                <b-dropdown-item @click="selectAll(true)"
                  >모든턴</b-dropdown-item
                >
                <b-dropdown-item @click="selectStep(0, 2)"
                  >홀수턴</b-dropdown-item
                >
                <b-dropdown-item @click="selectStep(1, 2)"
                  >짝수턴</b-dropdown-item
                >
                <b-dropdown-divider></b-dropdown-divider>

                <b-dropdown-text
                  v-for="spanIdx in [3, 4, 5, 6, 7]"
                  :key="spanIdx"
                >
                  {{ spanIdx }}턴 간격<br />
                  <b-button-group>
                    <b-button
                      class="ignoreMe"
                      v-for="beginIdx in spanIdx"
                      :key="beginIdx"
                      @click="selectStep(beginIdx - 1, spanIdx)"
                      >{{ beginIdx }}</b-button
                    >
                  </b-button-group>
                </b-dropdown-text>
              </b-dropdown>
            </div>
            <div class="col d-grid">
              <b-dropdown right text="반복">
                <b-dropdown-item v-for="turnIdx in maxPushTurn" :key="turnIdx"
                  >{{ turnIdx }}턴
                </b-dropdown-item>
              </b-dropdown>
            </div>
          </div>
        </td>
      </tr>
    </thead>
    <tbody class="center" style="font-weight: bold">
      <tr
        v-for="turnIdx in Math.min(maxTurn, viewMaxTurn)"
        :key="turnIdx"
        height="28"
        :id="`command_${turnIdx - 1}`"
        :class="pressed[turnIdx - 1] ? 'pressed' : ''"
      >
        <td width="32" class="idx_pad center bg0 d-grid" @click="clickTurn(turnIdx - 1)">
          <b-button
            size="sm"
            :variant="pressed[turnIdx - 1] ? 'info' : 'primary'"
            >{{ turnIdx }}</b-button
          >
        </td>
        <td @click="clickTurn(turnIdx - 1)"
          height="24"
          class="month_pad center bg1"
          style="min-width: 70px; white-space: nowrap; overflow: hidden"
        ></td>
        <td @click="clickTurn(turnIdx - 1)"
          width="38"
          class="time_pad center"
          style="background-color: black; white-space: nowrap; overflow: hidden"
        ></td>
        <td width="160" class="turn_pad center bg2">
          <span class="turn_text"></span>
        </td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="4">
          <div class="row gx-1">
            <div class="col-4 d-grid">
              <b-dropdown right split text="미루기">
                <b-dropdown-item v-for="turnIdx in maxPushTurn" :key="turnIdx"
                  >{{ turnIdx }}턴
                </b-dropdown-item>
              </b-dropdown>
            </div>
            <div class="col-8 d-grid"><b-button @click="toggleViewMaxTurn">{{flippedMaxTurn==viewMaxTurn?'펼치기':'접기'}}</b-button></div>
          </div>
        </td>
      </tr>
    </tfoot>
  </table>
  <div class="row gx-0">
      <div class="col-10 d-grid">
    <b-form-select v-model="selectedCommand"
      ><b-form-select-option-group
        v-for="cgroup in commandList"
        :key="cgroup['category']"
        :label="cgroup['category']"
      ><b-form-select-option v-for="(citem, ckey) in cgroup['values']" :value="ckey" :key="ckey">{{citem.title}}{{citem.possible?'':'(불가)'}}</b-form-select-option>
      </b-form-select-option-group
    ></b-form-select></div>
    <div class="col-2 d-grid">
    <b-button>실행</b-button></div>
  </div>
</template>

<script lang="ts">
import addMilliseconds from "date-fns/esm/addMilliseconds/index.js";
import { range } from "lodash";
import { defineComponent, ref } from "vue";
import { formatTime } from "./util/formatTime";
import { parseTime } from "./util/parseTime";

type commandItem = {
  title: string;
  compensation: number;
  possible: boolean;
};

declare const maxTurn: number;
declare const maxPushTurn: number;
declare const commandList: {
  category: string;
  values: Record<string, commandItem>[];
}[];
declare const serverNow: string;

export default defineComponent({
  name: "ReservedCommand",

  methods: {
    updateNow() {
      const clientNow = addMilliseconds(new Date(), this.timeDiff);
      this.serverNow = formatTime(clientNow, false);
      setTimeout(() => {
        this.updateNow();
      }, 250);
    },
    clickTurn(turnIdx: number) {
      this.pressed[turnIdx] = !this.pressed[turnIdx];
    },
    selectAll(e: Event | true) {
      //NOTE: split 구현에 버그가 있어서, 수동으로 구분해야함
      if (e !== true) {
        if (!e.target) {
          return;
        }
        if (
          (e.target as HTMLElement).classList.contains("dropdown-item") ||
          (e.target as HTMLElement).classList.contains(
            "dropdown-toggle-split"
          ) ||
          (e.target as HTMLElement).classList.contains("ignoreMe")
        ) {
          return;
        }
      }

      let pressedCnt = 0;
      for (const pressed of this.pressed) {
        if (pressed) {
          pressedCnt += 1;
        }
      }

      if (pressedCnt * 3 > this.maxTurn) {
        this.pressed.fill(false);
      } else {
        this.pressed.fill(true);
      }
    },
    selectStep(begin: number, step: number) {
      for (const idx of range(0, maxTurn)) {
        if ((idx - begin) % step == 0) {
          this.pressed[idx] = true;
        } else {
          this.pressed[idx] = false;
        }
      }
    },
    toggleViewMaxTurn(){
        if(this.viewMaxTurn == this.flippedMaxTurn){
            this.viewMaxTurn = this.maxTurn;
        }
        else{
            this.viewMaxTurn = this.flippedMaxTurn;
        }
    }
  },
  data() {
    const serverNowObj = parseTime(serverNow);
    const clientNowObj = new Date();
    const timeDiff = serverNowObj.getTime() - clientNowObj.getTime();

    setTimeout(() => {
      this.updateNow();
    }, 250);

    const pressed = Array.from<boolean>({ length: maxTurn }).fill(false);
    pressed[0] = true;

    const selectedCommand = ref<string>("휴식");

    return {
      maxTurn,
      flippedMaxTurn: 18,
      viewMaxTurn: 18,
      maxPushTurn,
      commandList,
      serverNow,
      timeDiff,
      pressed,
      selectedCommand,
    };
  },
});
</script>