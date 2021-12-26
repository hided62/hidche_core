<template>
  <div class="commandPad">
    <div class="col alert alert-dark m-0 p-1 center">
      <h4 class="m-0">명령 목록</h4>
    </div>

    <div class="row gx-1">
      <div class="col d-grid">
        <b-dropdown left text="턴 선택">
          <b-dropdown-item @click="selectTurn()">해제</b-dropdown-item>
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
      <DragSelect
        :style="rowGridStyle"
        attribute="turnIdx"
        @dragStart="isDragToggle = true"
        @dragDone="
          isDragToggle = false;
          toggleTurn(...$event);
        "
        v-slot="{ selected }"
      >
        <div
          v-for="(turnObj, turnIdx) in reservedCommandList.slice(
            0,
            viewMaxTurn
          )"
          :turnIdx="turnIdx"
          :key="turnIdx"
          class="idx_pad center d-grid"
        >
          <b-button
            size="sm"
            :variant="
              (isDragToggle && selected.has(`${turnIdx}`))?'light':
              turnList.has(turnIdx)
                ? 'info'
                : turnList.size == 0 && prevTurnList.has(turnIdx)
                ? 'success'
                : 'primary'
            "
            >{{ turnIdx + 1 }}</b-button
          >
        </div>
      </DragSelect>
      <DragSelect
        :style="rowGridStyle"
        attribute="turnIdx"
        @dragStart="isDragSingle = true"
        @dragDone="
          isDragSingle = false;
          selectTurn(...$event);
        "
        v-slot="{ selected }"
      >
        <div
          v-for="(turnObj, turnIdx) in reservedCommandList.slice(
            0,
            viewMaxTurn
          )"
          :key="turnIdx"
          height="24"
          class="month_pad center"
          :turnIdx="turnIdx"
          :style="{
            'white-space': 'nowrap',
            'font-size': `${Math.min(
              14,
              (75 / (`${turnObj.year ?? 1}`.length + 8)) * 1.8
            )}px`,
            overflow: 'hidden',
            color:
              isDragSingle && selected.has(`${turnIdx}`) ? 'cyan' : undefined,
          }"
        >
          {{ turnObj.year ? `${turnObj.year}年` : "" }}
          {{ turnObj.month ? `${turnObj.month}月` : "" }}
        </div>
      </DragSelect>
      <div :style="rowGridStyle">
        <div
          v-for="(turnObj, turnIdx) in reservedCommandList.slice(
            0,
            viewMaxTurn
          )"
          :key="turnIdx"
          class="time_pad center"
          :style="{
            backgroundColor: 'black',
            whiteSpace: 'nowrap',
            overflow: 'hidden',
          }"
        >
          {{ turnObj.time }}
        </div>
      </div>
      <div :style="rowGridStyle">
        <div
          v-for="(turnObj, turnIdx) in reservedCommandList.slice(
            0,
            viewMaxTurn
          )"
          :key="turnIdx"
          class="turn_pad center"
        >
          <span
            class="turn_text"
            :style="turnObj.style"
            v-b-tooltip.hover
            :title="turnObj.tooltip"
            v-html="turnObj.brief"
          ></span>
        </div>
      </div>
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
      <div class="col-2 d-grid">
        <b-button
          :pressed="searchModeOn"
          @click="toggleSearchCommand()"
          :variant="searchModeOn ? 'info' : 'primary'"
          v-b-tooltip.hover
          title="검색 기능을 활성화합니다."
          ><i class="bi bi-search"></i
        ></b-button>
      </div>
      <div class="col-7">
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
          :searchable="searchModeOn"
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
              <span :class="[props.option.possible ? '' : 'commandImpossible']">
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
import { isString, range } from "lodash";
import { stringifyUrl } from "query-string";
import { defineComponent, ref, watch } from "vue";
import { formatTime } from "@util/formatTime";
import { joinYearMonth } from "@util/joinYearMonth";
import { mb_strwidth } from "@util/mb_strwidth";
import { parseTime } from "@util/parseTime";
import { parseYearMonth } from "@util/parseYearMonth";
import { sammoAPI } from "@util/sammoAPI";
import { convertSearch초성 } from "./util/convertSearch초성";
import DragSelect from "@/components/DragSelect.vue";

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

const searchModeKey = `sammo_searchModeOn`;

export default defineComponent({
  name: "PartialReservedCommand",
  components: {
    DragSelect,
  },
  methods: {
    updateNow() {
      const serverNow = addMilliseconds(new Date(), this.timeDiff);
      this.serverNow = formatTime(serverNow, "HH:mm:ss");
      setTimeout(() => {
        this.updateNow();
      }, 1000 - serverNow.getMilliseconds());
    },
    toggleTurn(...turnList: number[] | string[]) {
      for (let turnIdx of turnList) {
        if (isString(turnIdx)) {
          turnIdx = parseInt(turnIdx);
        }
        if (this.turnList.has(turnIdx)) {
          this.turnList.delete(turnIdx);
        } else {
          this.turnList.add(turnIdx);
        }
      }
    },
    selectTurn(...turnList: number[] | string[]) {
      this.turnList.clear();
      for (const turnIdx of turnList) {
        if (isString(turnIdx)) {
          this.turnList.add(parseInt(turnIdx));
        } else {
          this.turnList.add(turnIdx);
        }
      }
    },
    selectAll(e: Event | true) {
      //NOTE: split 구현에 버그가 있어서, 수동으로 구분해야함
      if (e !== true && isDropdownChildren(e)) {
        return;
      }

      if (this.turnList.size * 3 > this.maxTurn) {
        this.turnList.clear();
      } else {
        for (let i = 0; i < this.maxTurn; i++) {
          this.turnList.add(i);
        }
      }
    },
    selectStep(begin: number, step: number) {
      this.turnList.clear();
      for (const idx of range(0, maxTurn)) {
        if ((idx - begin) % step == 0) {
          this.turnList.add(idx);
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

          tooltip.push(
            `자율 행동 기간: ${autorunLimitYear}년 ${autorunLimitMonth}월까지`
          );
        }

        if (mb_strwidth(brief) > 22) {
          tooltip.push(brief);
        }

        reservedCommandList.push({
          ...obj,
          year,
          month,
          time: formatTime(nextTurnTime, "HH:mm"),
          tooltip: tooltip.length == 0 ? undefined : tooltip.join("\n"),
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
      let turnList: number[];
      if (this.turnList.size == 0) {
        turnList = Array.from(this.prevTurnList.values());
      } else {
        turnList = Array.from(this.turnList.values());
      }

      if (turnList.length == 0) {
        turnList.push(0);
      }

      const commandName = this.selectedCommand.value;

      if (listReqArgCommand.has(commandName)) {
        document.location.href = stringifyUrl({
          url: "v_processing.php",
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

        if (this.turnList.size > 0) {
          this.prevTurnList.clear();
          for (const v of this.turnList) {
            this.prevTurnList.add(v);
          }
          this.turnList.clear();
        }
      } catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
      }
      await this.reloadCommandList();
    },
    toggleSearchCommand() {
      const searchModeOn = !this.searchModeOn;
      this.searchModeOn = searchModeOn;
      localStorage.setItem(searchModeKey, searchModeOn ? "1" : "0");
    },
  },
  data() {
    const serverNowObj = parseTime(serverNow);
    const clientNowObj = new Date();
    const timeDiff = serverNowObj.getTime() - clientNowObj.getTime();

    setTimeout(() => {
      this.updateNow();
    }, 1000 - serverNowObj.getMilliseconds());

    const selectedCommand = commandList[0].values[0];
    for (const subCategory of commandList) {
      for (const command of subCategory.values) {
        if (command.searchText) {
          continue;
        }
        command.searchText = convertSearch초성(command.simpleName).join("|");
      }
    }

    const searchModeOn = (localStorage.getItem(searchModeKey) ?? "0") != "0";

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

    const prevTurnList = new Set([0]);
    const turnList = new Set<number>();

    return {
      maxTurn,

      maxPushTurn,
      commandList,
      serverNow: formatTime(serverNowObj, "HH:mm:ss"),
      timeDiff,
      prevTurnList,
      turnList,
      selectedCommand,
      reservedCommandList: emptyTurn,
      autorun_limit: null as null | number,
      searchModeOn,
    };
  },
  setup() {
    const flippedMaxTurn = 15;
    const viewMaxTurn = ref(flippedMaxTurn);
    const rowGridStyle = ref({
      display: "grid",
      gridTemplateRows: `repeat(${viewMaxTurn.value}, 29.4px)`,
    });

    watch(viewMaxTurn, (val) => {
      rowGridStyle.value.gridTemplateRows = `repeat(${val}, 29.4px)`;
    });

    const isDragSingle = ref(false);
    const isDragToggle = ref(false);

    return {
      isDragSingle,
      isDragToggle,
      flippedMaxTurn,
      viewMaxTurn,
      rowGridStyle,
    };
  },
  mounted() {
    void this.reloadCommandList();
  },
});
</script>
<style lang="scss">
@use "sass:color";

@import "@scss/common/break_500px.scss";
@import "@scss/common/variables.scss";
@import "@scss/common/bootswatch_custom_variables.scss";
@import "@scss/game_bg.scss";

.commandPad {
  background-color: $gray-900;
}

.commandTable {
  width: 100%;
  display: grid;
  grid-template-columns: minmax(30px, 1fr) minmax(75px, 2.5fr) minmax(40px, 1fr) 5fr;
  //30, 70, 37.65, 160
}

@include media-1000px {
  .commandPad {
    margin-left: 10px;

    .turn_pad {
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .multiselect__content-wrapper {
      margin-left: calc(-100% / 7 * 2);
      width: calc(100% / 7 * 12);
    }

    .multiselect__single {
      display: inline-block;
      text-overflow: ellipsis;
      white-space: nowrap;
      overflow: hidden;
    }
  }
}

@include media-500px {
  .dropdown-item {
    padding: 8px;
  }

  .commandPad {
    margin-top: 10px;
    margin-bottom: 10px;

    .btn {
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
  background-color: $nbase2color;
}

.turn_pad:nth-child(8n) {
  background-color: color.adjust($nbase2color, $lightness: -5%);
}

.turn_pad .turn_text {
  display: inline-block;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
}
</style>