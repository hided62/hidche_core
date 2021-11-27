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
              <b-dropdown
                right
                split
                text="당기기"
                @click="pullGeneralCommandSingle"
              >
                <b-dropdown-item
                  v-for="turnIdx in maxPushTurn"
                  :key="turnIdx"
                  @click="pushGeneralCommand(-turnIdx)"
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
                <b-dropdown-item
                  v-for="turnIdx in maxPushTurn"
                  :key="turnIdx"
                  @click="repeatGeneralCommand(turnIdx)"
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
        v-for="(turnObj, turnIdx) in reservedCommandList.slice(
          0,
          Math.min(maxTurn, viewMaxTurn)
        )"
        :key="turnIdx"
        height="28"
        :id="`command_${turnIdx}`"
        :class="pressed[turnIdx] ? 'pressed' : ''"
      >
        <td
          width="32"
          class="idx_pad center bg0 d-grid"
          @click="clickTurn(turnIdx)"
        >
          <b-button
            size="sm"
            :variant="pressed[turnIdx] ? 'info' : 'primary'"
            >{{ turnIdx + 1 }}</b-button
          >
        </td>
        <td
          @click="clickTurn(turnIdx)"
          height="24"
          class="month_pad center bg1"
          :style="{
            'min-width': '70px',
            'white-space': 'nowrap',
            'font-size': `${Math.min(
              14,
              (70 / (`${turnObj.year ?? 1}`.length + 8)) * 1.8
            )}px`,
            overflow: 'hidden',
          }"
        >
          {{ turnObj.year ? `${turnObj.year}年` : "" }}
          {{ turnObj.month ? `${turnObj.month}月` : "" }}
        </td>
        <td
          @click="clickTurn(turnIdx)"
          width="38"
          class="time_pad center"
          style="background-color: black; white-space: nowrap; overflow: hidden"
        >
          {{ turnObj.time }}
        </td>
        <td width="160" class="turn_pad center bg2">
          <span
            class="turn_text"
            :style="turnObj.style"
            v-b-tooltip.hover
            :title="turnObj.tooltip"
            v-html="turnObj.brief"
          ></span>
        </td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="4">
          <div class="row gx-1">
            <div class="col-4 d-grid">
              <b-dropdown
                right
                split
                text="미루기"
                @click="pushGeneralCommandSingle"
              >
                <b-dropdown-item
                  v-for="turnIdx in maxPushTurn"
                  :key="turnIdx"
                  @click="pushGeneralCommand(turnIdx)"
                  >{{ turnIdx }}턴
                </b-dropdown-item>
              </b-dropdown>
            </div>
            <div class="col-8 d-grid">
              <b-button @click="toggleViewMaxTurn">{{
                flippedMaxTurn == viewMaxTurn ? "펼치기" : "접기"
              }}</b-button>
            </div>
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
          ><b-form-select-option
            v-for="(citem, ckey) in cgroup['values']"
            :value="ckey"
            :key="ckey"
            >{{ citem.title
            }}{{ citem.possible ? "" : "(불가)" }}</b-form-select-option
          >
        </b-form-select-option-group></b-form-select
      >
    </div>
    <div class="col-2 d-grid">
      <b-button @click="reserveCommand()">실행</b-button>
    </div>
  </div>
</template>

<script lang="ts">
import addMilliseconds from "date-fns/esm/addMilliseconds";
import addMinutes from "date-fns/esm/addMinutes";
import { range } from "lodash";
import { stringifyUrl } from "query-string";
import { defineComponent } from "vue";
import { formatTime } from "./util/formatTime";
import { joinYearMonth } from "./util/joinYearMonth";
import { parseTime } from "./util/parseTime";
import { parseYearMonth } from "./util/parseYearMonth";
import { sammoAPI } from "./util/sammoAPI";
import { unwrap_any } from "./util/unwrap_any";
type commandItem = {
  title: string;
  compensation: number;
  possible: boolean;
  reqArg: boolean;
};

declare const maxTurn: number;
declare const maxPushTurn: number;
declare const commandList: {
  category: string;
  values: Record<string, commandItem>;
}[];
declare const serverNow: string;

type TurnObj = {
  action: string;
  brief: string;
  arg: null | [] | Record<string, number | string | number[] | string[]>;
};

type TurnObjWithTime = TurnObj & {
  time: string;
  year?: number;
  month?: number;
  tooltip?: string;
  style?: Record<string, unknown>;
};

type ReservedCommandResponse = {
  result: true;
  turnTime: string;
  turnTerm: number;
  year: number;
  month: number;
  date: string;
  turn: TurnObj[];
  autorun_limit: null | number;
};

const listReqArgCommand = new Set<string>();
for (const commandCategories of commandList) {
  if (!commandCategories.values) {
    continue;
  }
  for (const [commandName, commandObj] of Object.entries(
    commandCategories.values
  )) {
    if (!commandObj.reqArg) {
      continue;
    }
    listReqArgCommand.add(commandName);
  }
}

function isDropdownChildren(e?: Event): boolean {
  if (!e) {
    return false;
  }
  if (!e.target) {
    return false;
  }
  if (
    (e.target as HTMLElement).classList.contains("dropdown-item") ||
    (e.target as HTMLElement).classList.contains("dropdown-toggle-split") ||
    (e.target as HTMLElement).classList.contains("ignoreMe")
  ) {
    return true;
  }
  return false;
}

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
      if (e !== true && isDropdownChildren(e)) {
        return;
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
    toggleViewMaxTurn() {
      if (this.viewMaxTurn == this.flippedMaxTurn) {
        this.viewMaxTurn = this.maxTurn;
      } else {
        this.viewMaxTurn = this.flippedMaxTurn;
      }
    },
    async repeatGeneralCommand(amount: number) {
      try {
        await sammoAPI(`Command/RepeatCommand`, { amount });
      } catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
      }
      await this.reloadCommandList();
    },
    async pushGeneralCommand(amount: number) {
      try {
        await sammoAPI("Command/PushCommand", { amount });
      } catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
      }
      await this.reloadCommandList();
    },
    pushGeneralCommandSingle(e: Event) {
      //NOTE: split 구현에 버그가 있어서, 수동으로 구분해야함
      if (isDropdownChildren(e)) {
        return;
      }
      void this.pushGeneralCommand(1);
    },
    pullGeneralCommandSingle(e: Event) {
      //NOTE: split 구현에 버그가 있어서, 수동으로 구분해야함
      if (isDropdownChildren(e)) {
        return;
      }
      void this.pushGeneralCommand(-1);
    },
    async reloadCommandList() {
      let result: ReservedCommandResponse;
      try {
        result = await sammoAPI("Command/GetReservedCommand");
      } catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
      }

      const reservedCommandList: TurnObjWithTime[] = [];
      let yearMonth = joinYearMonth(result.year, result.month);

      const turnTime = parseTime(result.turnTime);
      let nextTurnTime = new Date(turnTime);

      const autorunLimitYearMonth = result.autorun_limit ?? yearMonth - 1;
      const [autorunLimitYear, autorunLimitMonth] = parseYearMonth(
        autorunLimitYearMonth
      );

      for (const obj of result.turn) {
        const [year, month] = parseYearMonth(yearMonth);
        let tooltip: string | undefined = undefined;
        let style: Record<string, unknown> = {};
        if (yearMonth <= autorunLimitYearMonth) {
          if (obj.brief == "휴식") {
            obj.brief = "휴식<small>(자율 행동)</small>";
          }
          style.color = "#aaffff";
          tooltip = `자율 행동 기간: ${autorunLimitYear}년 ${autorunLimitMonth}월까지`;
        }

        reservedCommandList.push({
          ...obj,
          year,
          month,
          time: formatTime(nextTurnTime, "HH:mm"),
          tooltip,
          style,
        });

        yearMonth += 1;
        nextTurnTime = addMinutes(nextTurnTime, result.turnTerm);
      }
      this.reservedCommandList = reservedCommandList;

      const serverNowObj = parseTime(result.date);
      const clientNowObj = new Date();
      const timeDiff = serverNowObj.getTime() - clientNowObj.getTime();
      this.timeDiff = timeDiff;
    },
    async reserveCommand() {
      const turnList: number[] = [];
      for (const [turnIdx, pressed] of this.pressed.entries()) {
        if (!pressed) {
          continue;
        }
        turnList.push(turnIdx);
      }

      if (listReqArgCommand.has(this.selectedCommand)) {
        document.location.href = stringifyUrl({
          url: "b_processing.php",
          query: {
            command: unwrap_any<string>(this.selectedCommand),
            turnList: turnList.join("_"),
          },
        });
        return;
      }

      try {
        await sammoAPI("Command/ReserveCommand", {
          turnList,
          action: this.selectedCommand,
        });
      } catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
      }
      await this.reloadCommandList();
    },
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

    const selectedCommand = "휴식";

    const emptyTurn: TurnObjWithTime[] = Array.from<TurnObjWithTime>({
      length: maxTurn,
    }).fill({
      arg: null,
      brief: "",
      action: "",
      year: undefined,
      month: undefined,
      time: "",
    });

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
      reservedCommandList: emptyTurn,
      autorun_limit: null as null | number,
    };
  },
  mounted() {
    void this.reloadCommandList();
  },
});
</script>