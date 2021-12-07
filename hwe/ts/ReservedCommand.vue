<template>
  <div class="commandPad">
    <div class="col alert alert-dark m-0 p-1 center">
      <h4 class="m-0">명령 목록</h4>
    </div>

    <div class="row gx-1">
      <div class="col d-grid">
        <b-dropdown left text="턴 선택">
          <b-dropdown-item @click="selectAll(true)">모든턴</b-dropdown-item>
          <b-dropdown-item @click="selectStep(0, 2)">홀수턴</b-dropdown-item>
          <b-dropdown-item @click="selectStep(1, 2)">짝수턴</b-dropdown-item>
          <b-dropdown-divider></b-dropdown-divider>

          <b-dropdown-text v-for="spanIdx in [3, 4, 5, 6, 7]" :key="spanIdx">
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
      <div
        class="col alert alert-primary m-0 p-0"
        style="
          text-align: center;
          display: flex;
          justify-content: center;
          align-items: center;
        "
      >
        {{ serverNow }}
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

    <div class="commandTable">
      <template
        v-for="(turnObj, turnIdx) in reservedCommandList.slice(
          0,
          Math.min(maxTurn, viewMaxTurn)
        )"
        :key="turnIdx"
        height="28"
        :id="`command_${turnIdx}`"
        :class="pressed[turnIdx] ? 'pressed' : ''"
      >
        <div class="idx_pad center d-grid" @click="toggleTurn(turnIdx)">
          <b-button
            size="sm"
            :variant="pressed[turnIdx] ? 'info' : ((turnIdx==0&&pressed.filter(t=>t).length==0)?'success':'primary')"
            >{{ turnIdx + 1 }}</b-button
          >
        </div>
        <div
          @click="selectTurn(turnIdx)"
          height="24"
          class="month_pad center"
          :style="{
            'white-space': 'nowrap',
            'font-size': `${Math.min(
              14,
              (75 / (`${turnObj.year ?? 1}`.length + 8)) * 1.8
            )}px`,
            overflow: 'hidden',
          }"
        >
          {{ turnObj.year ? `${turnObj.year}年` : "" }}
          {{ turnObj.month ? `${turnObj.month}月` : "" }}
        </div>
        <div
          class="time_pad center"
          style="background-color: black; white-space: nowrap; overflow: hidden"
        >
          {{ turnObj.time }}
        </div>
        <div class="turn_pad center">
          <span
            class="turn_text"
            :style="turnObj.style"
            v-b-tooltip.hover
            :title="turnObj.tooltip"
            v-html="turnObj.brief"
          ></span>
        </div>
      </template>
    </div>
    <div class="row gx-1">
      <div class="col d-grid">
        <b-dropdown right split text="당기기" @click="pullGeneralCommandSingle">
          <b-dropdown-item
            v-for="turnIdx in maxPushTurn"
            :key="turnIdx"
            @click="pushGeneralCommand(-turnIdx)"
            >{{ turnIdx }}턴
          </b-dropdown-item>
        </b-dropdown>
      </div>
      <div class="col d-grid">
        <b-dropdown right split text="미루기" @click="pushGeneralCommandSingle">
          <b-dropdown-item
            v-for="turnIdx in maxPushTurn"
            :key="turnIdx"
            @click="pushGeneralCommand(turnIdx)"
            >{{ turnIdx }}턴
          </b-dropdown-item>
        </b-dropdown>
      </div>
      <div class="col d-grid">
        <b-button @click="toggleViewMaxTurn">{{
          flippedMaxTurn == viewMaxTurn ? "펼치기" : "접기"
        }}</b-button>
      </div>
    </div>
    <div class="row gx-0">
      <div class="col-9">
        <v-multiselect
          v-model="selectedCommand"
          :allow-empty="false"
          :options="commandList"
          :group-select="false"
          group-values="values"
          group-label="category"
          label="searchText"
          track-by="value"
          open-direction="top"
          :show-labels="false"
          selectLabel="선택(엔터)"
          selectGroupLabel=""
          selectedLabel="선택됨"
          deselectLabel="해제(엔터)"
          deselectGroupLabel=""
          placeholder="턴 선택"
          :maxHeight="400"
        >
          <template v-slot:noResult>검색 결과가 없습니다.</template>
          <template v-slot:option="props"
            ><!--FIXME: 카테고리-->
            <template v-if="props.option.title">
              <span
                class="compensatePositive"
                v-if="props.option.compensation > 0"
                >▲</span
              >
              <span
                class="compensateNegative"
                v-else-if="props.option.compensation < 0"
                >▼</span
              >
              <span class="compensateNeutral" v-else></span>
              <span :class="[
              props.option.possible?'':'commandImpossible',
              ]">
                {{ props.option.title }}
              </span>

            </template>
            <template v-else-if="props.option.category">
              {{ props.option.category }}
            </template>
          </template>
          <template v-slot:singleLabel="props">
            {{ props.option.simpleName }}
          </template>
        </v-multiselect>
      </div>
      <div class="col-3 d-grid">
        <b-button @click="reserveCommand()" variant="primary">실행</b-button>
      </div>
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
import { mb_strwidth } from "./util/mb_strwidth";
import { parseTime } from "./util/parseTime";
import { parseYearMonth } from "./util/parseYearMonth";
import { sammoAPI } from "./util/sammoAPI";
import { filter초성withAlphabet } from "./util/filter초성withAlphabet";

type commandItem = {
  value: string;
  title: string;
  compensation: number;
  simpleName: string;
  possible: boolean;
  reqArg: boolean;
  searchText?: string;
};

declare const maxTurn: number;
declare const maxPushTurn: number;
declare const commandList: {
  category: string;
  values: commandItem[];
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
  for (const commandObj of commandCategories.values) {
    if (!commandObj.reqArg) {
      continue;
    }
    listReqArgCommand.add(commandObj.value);
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
      const serverNow = addMilliseconds(new Date(), this.timeDiff);
      this.serverNow = formatTime(serverNow, "HH:mm:ss");
      setTimeout(() => {
        this.updateNow();
      }, 1000 - serverNow.getMilliseconds());
    },
    toggleTurn(turnIdx: number) {
      this.pressed[turnIdx] = !this.pressed[turnIdx];
    },
    selectTurn(turnIdx: number) {
      this.pressed.fill(false);
      this.pressed[turnIdx] = true;
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
        let tooltip: string[] = [];
        let style: Record<string, unknown> = {};

        const brief = obj.brief;

        if (yearMonth <= autorunLimitYearMonth) {
          if (obj.brief == "휴식") {
            obj.brief = "휴식<small>(자율 행동)</small>";
          }
          style.color = "#aaffff";

          tooltip.push(`자율 행동 기간: ${autorunLimitYear}년 ${autorunLimitMonth}월까지`);
        }

        if(mb_strwidth(brief) > 22){
          tooltip.push(brief);
        }

        reservedCommandList.push({
          ...obj,
          year,
          month,
          time: formatTime(nextTurnTime, "HH:mm"),
          tooltip: tooltip.length==0?undefined:tooltip.join("\n"),
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

      if(turnList.length == 0){
        turnList.push(0);
      }

      const commandName = this.selectedCommand.value;

      if (listReqArgCommand.has(commandName)) {
        document.location.href = stringifyUrl({
          url: "b_processing.php",
          query: {
            command: commandName,
            turnList: turnList.join("_"),
          },
        });
        return;
      }

      try {
        await sammoAPI("Command/ReserveCommand", {
          turnList,
          action: commandName,
        });
        this.pressed.fill(false);
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
    }, 1000 - serverNowObj.getMilliseconds());

    const pressed = Array.from<boolean>({ length: maxTurn }).fill(false);
    //pressed[0] = true;

    const selectedCommand = commandList[0].values[0];
    for(const subCategory of commandList){
      for(const command of subCategory.values){
        if(command.searchText){
          continue;
        }
        const [filteredTextH, filteredTextA] = filter초성withAlphabet(command.simpleName.replace(/\s+/g, ''));
        command.searchText = `${command.simpleName} ${filteredTextH} ${filteredTextA}`
      }
    }

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
      flippedMaxTurn: 16,
      viewMaxTurn: 16,
      maxPushTurn,
      commandList,
      serverNow: formatTime(serverNowObj, "HH:mm:ss"),
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
<style lang="scss">
@import "../scss/break_500px.scss";
@import "../scss/variables.scss";
@import "../scss/bootswatch_custom_variables.scss";
@import "../../node_modules/bootstrap5/scss/bootstrap-utilities.scss";

.commandPad {
  background-color: $gray-900;
}

.commandTable {
  width: 100%;
  display: grid;
  grid-template-columns: minmax(30px, 1fr) minmax(75px, 2.5fr) minmax(40px, 1fr) 5fr;
  //30, 70, 37.65, 160
}

@include media-breakpoint-up(md) {
  .commandPad {
    margin-left: 10px;

    .turn_pad {

      overflow: hidden;
      text-overflow: ellipsis;
    }
    .multiselect__content-wrapper {
      width: 133.3%;
    }

    .multiselect__single {
      display: inline-block;
      text-overflow: ellipsis;
      white-space: nowrap;
      overflow: hidden;
    }
  }
}

@include media-breakpoint-down(md) {
  .dropdown-item {
    padding: 8px;
  }

  .commandPad {
    margin-top: 10px;
    margin-bottom: 10px;

    .btn{
      transition: none !important;
    }
  }

  .month_pad,
  .time_pad,
  .turn_pad {
    padding: 6px;
  }
}

.month_pad:hover {
  text-decoration: underline;
  cursor: pointer;
}

.month_pad,
.time_pad,
.turn_pad {
  display: flex;
  justify-content: center;
  align-items: center;
}

.turn_pad {
  white-space: nowrap;
  background-color: rgba($base1color, 0.5);
}

.turn_pad .turn_text {
  display: inline-block;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
}
</style>