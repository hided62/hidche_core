<template>
  <div id="container" class="pageChiefCenter">
    <TopBackBar title="사령부" reloadable @reload="reloadTable" />

    <div
      id="mainTable"
      v-if="chiefList !== undefined"
      :class="`${targetIsMe ? 'targetIsMe' : 'targetIsNotMe'}`"
    >
      <template
        v-for="(chiefLevel, vidx) in [12, 10, 8, 6, 11, 9, 7, 5]"
        :key="chiefLevel"
      >
        <div
          v-if="vidx % 4 == 0"
          :class="[
            'turnIdx',
            vidx == 0 && !targetIsMe ? undefined : 'only1000px',
          ]"
        >
          <div :class="['subRows', 'bg0']" :style="mainTableGridRows">
            <div class="bg1">&nbsp;</div>
            <div
              v-for="idx in maxChiefTurn"
              :class="[`turnIdxLeft`, 'align-self-center', 'center']"
              :key="idx"
            >
              {{ idx }}
            </div>
          </div>
        </div>
        <div
          v-for="(officer, idx) in [chiefList[chiefLevel]]"
          :key="idx"
          :class="[`${viewTarget == chiefLevel ? '' : 'only1000px'}`]"
        >
          <TopItem
            v-if="officerLevel != chiefLevel"
            :style="mainTableGridRows"
            :officer="officer"
            :maxTurn="maxChiefTurn"
            :turnTerm="turnTerm"
          />
          <div class="commandBox" v-else>
            <div
              class="only1000px bg1 center row gx-0"
              style="height: 24px; font-size: 1.2em"
            >
              <div class="col-5 align-self-center text-end">
                {{ officer.officerLevelText }} :
              </div>
              <div
                class="col-7 align-self-center"
                :style="{
                  color: getNpcColor(officer.npcType ?? 0),
                }"
              >
                {{ officer.name }}
              </div>
            </div>
            <div
              :class="[
                'row',
                'controlPad',
                chiefLevel == officerLevel ? 'targetIsMe' : 'targetIsNotMe',
              ]"
            >
              <div class="col-3 col-md-12 order-md-last">
                <div class="d-grid mb-1 py-1 only500px bg1 center">
                  <div
                    :style="{
                      color: getNpcColor(officer.npcType ?? 0),
                      fontSize: '1.2em',
                    }"
                  >
                    {{ officer.name }}
                  </div>
                  <div>{{ officer.officerLevelText }}</div>
                </div>
                <div class="row gx-1 gy-1 py-1">
                  <div class="col-md-4 mx-0 mb-0 mt-1 d-grid">
                    <div
                      class="alert alert-primary mb-0 center"
                      style="padding: 0.5rem 0"
                    >
                      {{ serverNow }}
                    </div>
                  </div>

                  <b-dropdown class="col-md-4" left text="턴 선택">
                    <b-dropdown-item @click="selectNone()"
                      >해제</b-dropdown-item
                    >
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
                  <b-dropdown class="col-md-4" text="반복">
                    <b-dropdown-item
                      v-for="turnIdx in maxPushTurn"
                      :key="turnIdx"
                      @click="repeatNationCommand(turnIdx)"
                      >{{ turnIdx }}턴
                    </b-dropdown-item>
                  </b-dropdown>
                  <b-dropdown
                    class="col-md-6"
                    split
                    text="당기기"
                    @click="pullNationCommandSingle"
                  >
                    <b-dropdown-item
                      v-for="turnIdx in maxPushTurn"
                      :key="turnIdx"
                      @click="pushNationCommand(-turnIdx)"
                      >{{ turnIdx }}턴
                    </b-dropdown-item>
                  </b-dropdown>
                  <b-dropdown
                    class="col-md-6"
                    split
                    text="미루기"
                    @click="pushNationCommandSingle"
                  >
                    <b-dropdown-item
                      v-for="turnIdx in maxPushTurn"
                      :key="turnIdx"
                      @click="pushNationCommand(turnIdx)"
                      >{{ turnIdx }}턴
                    </b-dropdown-item>
                  </b-dropdown>
                </div>
              </div>
              <div class="col">
                <ChiefReservedCommand
                  :key="idx"
                  :year="year"
                  :month="month"
                  :turn="officer.turn"
                  :turnTerm="turnTerm"
                  :commandList="unwrap(commandList)"
                  :turnTime="officer.turnTime"
                  :maxTurn="maxChiefTurn"
                  :maxPushTurn="Math.floor(maxChiefTurn / 2)"
                  :date="date"
                  v-model:selectedTurn="turnList"
                  @raiseReload="reloadTable()"
                />
              </div>
            </div>
          </div>
        </div>
        <div
          v-if="vidx % 4 == 3"
          :class="[
            'turnIdx',
            vidx == 7 && !targetIsMe ? undefined : 'only1000px',
          ]"
        >
          <div :class="['subRows', 'bg0']" :style="mainTableGridRows">
            <div class="bg1">&nbsp;</div>
            <div
              v-for="idx in maxChiefTurn"
              :class="[`turnIdxRight`, 'align-self-center', 'center']"
              :key="idx"
            >
              {{ idx }}
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
  <div id="bottomChiefBox" v-if="chiefList">
    <div id="bottomChiefList" class="c-bg2">
      <template
        v-for="(chiefLevel, vidx) in [12, 10, 8, 6, 11, 9, 7, 5]"
        :key="chiefLevel"
      >
        <div
          class="turnIdx subRows bg0"
          :style="subTableGridRows"
          v-if="vidx % 4 == 0"
        >
          <div class="bg1" style="grid-row: 1/3"></div>
          <div v-for="idx in maxChiefTurn" :class="[`turnIdxLeft`]" :key="idx">
            {{ idx }}
          </div>
        </div>
        <BottomItem
          :chiefLevel="chiefLevel"
          :chiefList="chiefList"
          :style="subTableGridRows"
          :isMe="chiefLevel == officerLevel"
          @click="viewTarget = chiefLevel"
        />
        <div
          class="turnIdx subRows bg0"
          :style="subTableGridRows"
          v-if="vidx % 4 == 3"
        >
          <div class="bg1" style="grid-row: 1/3"></div>
          <div v-for="idx in maxChiefTurn" :class="`turnIdxRight`" :key="idx">
            {{ idx }}
          </div>
        </div>
      </template>
    </div>
  </div>
  <div id="bottomBar">
    <BottomBar />
  </div>
</template>
<script lang="ts">
import "@scss/common/bootstrap5.scss";
import "@scss/game_bg.scss";
import "../../css/config.css";

import { computed, defineComponent, reactive, ref, toRefs, watch } from "vue";
import ChiefReservedCommand from "@/components/ChiefReservedCommand.vue";
import TopBackBar from "@/components/TopBackBar.vue";
import BottomBar from "@/components/BottomBar.vue";
import VueTypes from "vue-types";
import { isString, range } from "lodash";
import { mb_strwidth } from "./util/mb_strwidth";
import { entriesWithType } from "./util/entriesWithType";
import { addMilliseconds } from "date-fns";
import { formatTime } from "./util/formatTime";
import { parseTime } from "./util/parseTime";
import { getNpcColor } from "./common_legacy";
import TopItem from "@/ChiefCenter/TopItem.vue";
import BottomItem from "@/ChiefCenter/BottomItem.vue";
import type { ChiefResponse, OptionalFull } from "./defs";
import { SammoAPI } from "./SammoAPI";
import { unwrap } from "@/util/unwrap";

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
  components: {
    TopBackBar,
    BottomBar,
    ChiefReservedCommand,
    TopItem,
    BottomItem,
  },
  props: {
    maxChiefTurn: VueTypes.number.isRequired,
  },
  watch: {
    year() {
      console.log(this.lastExecute);
    },
  },
  methods: {
    toggleTurn(turnIdx: number) {
      if (this.turnList.has(turnIdx)) {
        this.turnList.delete(turnIdx);
      } else {
        this.turnList.add(turnIdx);
      }
    },
    selectTurn(turnIdx: number) {
      this.turnList.clear();
      this.turnList.add(turnIdx);
    },
    selectNone() {
      this.turnList.clear();
    },
    selectAll(e: Event | true) {
      //NOTE: split 구현에 버그가 있어서, 수동으로 구분해야함
      if (e !== true && isDropdownChildren(e)) {
        return;
      }

      if (this.turnList.size * 3 > this.maxChiefTurn) {
        this.turnList.clear();
      } else {
        for (let i = 0; i < this.maxChiefTurn; i++) {
          this.turnList.add(i);
        }
      }
    },
    selectStep(begin: number, step: number) {
      this.turnList.clear();
      for (const idx of range(0, this.maxChiefTurn)) {
        if ((idx - begin) % step == 0) {
          this.turnList.add(idx);
        }
      }
    },
    async repeatNationCommand(amount: number) {
      try {
        await SammoAPI.NationCommand.RepeatCommand({ amount });
      } catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
      }
      await this.reloadTable();
    },
    async pushNationCommand(amount: number) {
      try {
        await SammoAPI.NationCommand.PushCommand({ amount });
      } catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
      }
      await this.reloadTable();
    },
    pushNationCommandSingle(e: Event) {
      //NOTE: split 구현에 버그가 있어서, 수동으로 구분해야함
      if (isDropdownChildren(e)) {
        return;
      }
      void this.pushNationCommand(1);
    },
    pullNationCommandSingle(e: Event) {
      //NOTE: split 구현에 버그가 있어서, 수동으로 구분해야함
      if (isDropdownChildren(e)) {
        return;
      }
      void this.pushNationCommand(-1);
    },
  },
  setup(props) {
    const viewTarget = ref<number | undefined>();

    const turnList = ref(new Set<number>());

    const targetIsMe = ref<boolean>(false);
    watch(viewTarget, (val) => {
      console.log("targetChange!", val, targetIsMe);
      if (val === undefined) {
        targetIsMe.value = false;
        return;
      }
      if (tableObj.officerLevel === undefined) {
        targetIsMe.value = false;
      }
      targetIsMe.value = val === tableObj.officerLevel;
      console.log("result", targetIsMe.value);
    });

    const tableObj = reactive<Omit<OptionalFull<ChiefResponse>, "result">>({
      lastExecute: undefined,
      year: undefined,
      month: undefined,
      turnTerm: undefined,
      date: undefined,
      chiefList: undefined,
      isChief: undefined,
      autorun_limit: undefined,
      officerLevel: undefined,
      commandList: undefined,
    });

    async function reloadTable(): Promise<void> {
      try {
        const response =
          await SammoAPI.NationCommand.GetReservedCommand<ChiefResponse>();
        console.log(response);
        for (const [key, value] of entriesWithType(response)) {
          if (key === "result") {
            continue;
          }

          if (key === "officerLevel") {
            if (value < 5) {
              tableObj.officerLevel = undefined;
            } else {
              tableObj.officerLevel = value as number;
            }
            continue;
          }

          //HACK:
          tableObj[key as unknown as "year"] = value as unknown as undefined;
        }

        const serverNowObj = parseTime(response.date);
        const clientNowObj = new Date();
        timeDiff.value = serverNowObj.getTime() - clientNowObj.getTime();

        if (viewTarget.value === undefined) {
          if (!tableObj.officerLevel) {
            viewTarget.value = 12;
          } else {
            viewTarget.value = tableObj.officerLevel;
          }
        }
      } catch (e) {
        if (isString(e)) {
          alert(e);
        }
        console.error(e);
        return;
      }
    }
    //

    const mainTableGridRows = computed(() => {
      return {
        gridTemplateRows: `24px repeat(${props.maxChiefTurn}, 30px)`,
      };
    });

    const subTableGridRows = computed(() => {
      return {
        gridTemplateRows: `36px repeat(${props.maxChiefTurn + 1}, 1fr)`,
      };
    });

    void reloadTable();

    const serverNow = ref(formatTime(new Date(), "HH:mm:ss"));
    const timeDiff = ref(0);

    function updateNow() {
      const serverNowObj = addMilliseconds(new Date(), timeDiff.value);
      serverNow.value = formatTime(serverNowObj, "HH:mm:ss");
      setTimeout(() => {
        updateNow();
      }, 1000 - serverNowObj.getMilliseconds());
    }

    setTimeout(() => {
      updateNow();
    }, 500);

    return {
      updateNow,
      serverNow,
      timeDiff,
      turnList,
      ...toRefs(tableObj),
      mb_strwidth,
      mainTableGridRows,
      subTableGridRows,
      reloadTable,
      getNpcColor,
      unwrap,
      viewTarget,
      targetIsMe,
      maxPushTurn: props.maxChiefTurn / 2,
    };
  },
});
</script>
<style lang="scss">
@import "@scss/chiefCenter.scss";
</style>