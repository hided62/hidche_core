<template>
  <div class="commandPad chiefReservedCommand">
    <div class="commandTable">
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
          v-for="(turnObj, turnIdx) in reservedCommandList"
          :key="turnIdx"
          :turnIdx="turnIdx"
          class="time_pad center f_tnum"
          :style="{
            backgroundColor: 'black',
            whiteSpace: 'nowrap',
            overflow: 'hidden',
            color:
              isDragSingle && selected.has(`${turnIdx}`) ? 'cyan' : undefined,
          }"
        >
          {{ turnObj.time }}
        </div>
      </DragSelect>
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
          v-for="(turnObj, turnIdx) in reservedCommandList"
          :key="turnIdx"
          :turnIdx="turnIdx"
          class="idx_pad center d-grid"
        >
          <b-button
            size="sm"
            :variant="
              isDragToggle && selected.has(`${turnIdx}`)
                ? 'light'
                : turnList.has(turnIdx)
                ? 'info'
                : turnList.size == 0 && prevTurnList.has(turnIdx)
                ? 'success'
                : 'primary'
            "
            >{{ turnIdx + 1 }}</b-button
          >
        </div>
      </DragSelect>
      <div :style="rowGridStyle">
        <div
          v-for="(turnObj, turnIdx) in reservedCommandList"
          :key="turnIdx"
          class="turn_pad center"
          @click="chooseCommand(turnObj.action)"
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
import addMinutes from "date-fns/esm/addMinutes";
import { stringifyUrl } from "query-string";
import { defineComponent, PropType } from "vue";
import { formatTime } from "@util/formatTime";
import { joinYearMonth } from "@util/joinYearMonth";
import { mb_strwidth } from "@util/mb_strwidth";
import { parseTime } from "@util/parseTime";
import { parseYearMonth } from "@util/parseYearMonth";
import { convertSearch초성 } from "@util/convertSearch초성";
import VueTypes from "vue-types";
import DragSelect from "@/components/DragSelect.vue";
import { isString } from "lodash";
import { SammoAPI } from "@/SammoAPI";

type commandItem = {
  value: string;
  title: string;
  compensation: number;
  simpleName: string;
  possible: boolean;
  reqArg: boolean;
  searchText?: string;
};

/*declare const commandList: {
  category: string;
  values: commandItem[];
}[];*/
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

const searchModeKey = `sammo_searchModeOn`;

export default defineComponent({
  name: "NationReservedCommand",
  components: {
    DragSelect
  },
  props: {
    maxTurn: VueTypes.integer.isRequired,
    maxPushTurn: VueTypes.integer.isRequired,
    date: VueTypes.string.isRequired,
    year: VueTypes.integer.isRequired,
    month: VueTypes.integer.isRequired,
    turnTerm: VueTypes.integer.isRequired,
    turnTime: VueTypes.string.isRequired,
    selectedTurn: {
      type: Object as PropType<Set<number>>,
      required: false,
      default: () => new Set(),
    },
    turn: {
      type: Array as PropType<TurnObj[]>,
      required: true,
    },
    commandList: {
      type: Object as PropType<
        {
          category: string;
          values: commandItem[];
        }[]
      >,
      required: true,
    },
  },
  emits: ["raiseReload", "update:selectedTurn"],
  watch: {
    date() {
      this.triggerUpdateCommandList("date");
    },
    year() {
      this.triggerUpdateCommandList("year");
    },
    month() {
      this.triggerUpdateCommandList("month");
    },
    turnTime() {
      this.triggerUpdateCommandList("turnTime");
    },
    commandList() {
      this.triggerUpdateCommandList("commandList");
    },
    selectedTurn(val: Set<number>) {
      console.log(val);
      if (val === this.turnList) {
        console.log("pass!");
        return;
      }
      this.turnList.clear();
      for (const t of val.values()) {
        this.turnList.add(t);
      }
    },
    turnList: {
      handler() {
        console.log(this.turnList);
        this.$emit("update:selectedTurn", this.turnList);
      },
      deep: true,
    },
  },
  methods: {
    triggerUpdateCommandList(type?: string) {
      console.log("try update", type);
      this.updated = false;
      setTimeout(() => {
        this.updateCommandList();
      }, 1);
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
      this.$emit("update:selectedTurn", this.turnList);
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
      this.$emit("update:selectedTurn", this.turnList);
    },
    updateCommandList() {
      if (this.updated) {
        return;
      }
      console.log("do update!");
      const reservedCommandList: TurnObjWithTime[] = [];
      let yearMonth = joinYearMonth(this.year, this.month);

      const turnTime = parseTime(this.turnTime);
      let nextTurnTime = new Date(turnTime);

      const autorunLimitYearMonth = this.autorun_limit ?? yearMonth - 1;
      const [autorunLimitYear, autorunLimitMonth] = parseYearMonth(
        autorunLimitYearMonth
      );

      for (const obj of this.turn) {
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
          time: formatTime(
            nextTurnTime,
            this.turnTerm >= 5 ? "HH:mm" : "mm:ss"
          ),
          tooltip: tooltip.length == 0 ? undefined : tooltip.join("\n"),
          style,
        });

        yearMonth += 1;
        nextTurnTime = addMinutes(nextTurnTime, this.turnTerm);
      }
      this.reservedCommandList = reservedCommandList;

      const serverNowObj = parseTime(this.date);
      const clientNowObj = new Date();
      const timeDiff = serverNowObj.getTime() - clientNowObj.getTime();
      this.timeDiff = timeDiff;
      this.updated = true;
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

      if (this.listReqArgCommand.has(commandName)) {
        document.location.href = stringifyUrl({
          url: "v_processing.php",
          query: {
            command: commandName,
            turnList: turnList.join("_"),
            is_chief: true,
          },
        });
        return;
      }

      try {
        await SammoAPI.NationCommand.ReserveCommand({
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
      this.$emit("raiseReload");
    },
    toggleSearchCommand() {
      const searchModeOn = !this.searchModeOn;
      this.searchModeOn = searchModeOn;
      localStorage.setItem(searchModeKey, searchModeOn ? "1" : "0");
    },
    chooseCommand(val: string){
      this.selectedCommand = this.invCommandMap[val];
    },
  },
  data() {
    const serverNowObj = parseTime(this.date);
    const clientNowObj = new Date();
    const timeDiff = serverNowObj.getTime() - clientNowObj.getTime();

    const listReqArgCommand = new Set<string>();
    for (const commandCategories of this.commandList) {
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

    const selectedCommand = this.commandList[0].values[0];
    for (const subCategory of this.commandList) {
      for (const command of subCategory.values) {
        if (command.searchText) {
          continue;
        }
        command.searchText = convertSearch초성(command.simpleName).join("|");
      }
    }

    const invCommandMap: Record<string, commandItem> = {};
    for(const category of this.commandList){
      for(const command of category.values){
        invCommandMap[command.value] = command;
      }
    }

    const searchModeOn = (localStorage.getItem(searchModeKey) ?? "0") != "0";

    const emptyTurn: TurnObjWithTime[] = Array.from<TurnObjWithTime>({
      length: this.maxTurn,
    }).fill({
      arg: null,
      brief: "",
      action: "",
      year: undefined,
      month: undefined,
      time: "",
    });

    const prevTurnList = new Set([0]);

    const rowGridStyle = {
      display: "grid",
      gridTemplateRows: `repeat(${this.maxTurn}, 30px)`,
    };

    return {
      updated: false,
      listReqArgCommand,
      serverNow: formatTime(serverNowObj, "HH:mm:ss"),
      timeDiff,
      prevTurnList,
      turnList: this.selectedTurn,
      selectedCommand,
      reservedCommandList: emptyTurn,
      autorun_limit: null as null | number,
      searchModeOn,
      rowGridStyle,
      isDragSingle: false,
      isDragToggle: false,
      invCommandMap,
    };
  },
  mounted() {
    this.updateCommandList();
  },
});
</script>
<style lang="scss">
@import "@scss/common/break_500px.scss";
@import "@scss/common/variables.scss";
@import "@scss/common/bootswatch_custom_variables.scss";

.chiefReservedCommand {
  background-color: $gray-900;

  .commandTable {
    width: 100%;
    display: grid;
    grid-template-columns: minmax(39.67px, 1fr) minmax(28px, 1fr) 5fr;
    //30, 70, 37.65, 160
  }

  @include media-1000px {
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

  @include media-500px {
    .dropdown-item {
      padding: 8px;
    }

    .multiselect__content-wrapper {
      margin-left: calc(-100% / 7 * 2);
      width: calc(100% / 7 * 12);
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
  }

  .turn_pad .turn_text {
    display: inline-block;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
  }
}
</style>