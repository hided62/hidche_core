<template>
  <TopBackBar title="장수 생성" />

  <div id="container" class="bg0">
    <div class="nation-list">
      <div class="nation-header nation-row bg1 center">
        <div>국가명</div>
        <div>임관권유문</div>
        <div class="display-toggle d-grid">
          <b-button
            v-model="displayTable"
            :pressed="displayTable"
            :variant="displayTable ? 'info' : 'secondary'"
            @click="displayTable = !displayTable"
          >
            {{ displayTable ? "숨기기" : "보이기" }}
          </b-button>
        </div>
        <div class="zoom-toggle d-grid">
          <b-button
            v-model="toggleZoom"
            :pressed="toggleZoom"
            :variant="toggleZoom ? 'info' : 'secondary'"
            :disabled="!displayTable"
            @click="toggleZoom = !toggleZoom"
          >
            {{ toggleZoom ? "작게 보기" : "크게 보기" }}
          </b-button>
        </div>
      </div>
      <template v-if="displayTable">
        <div
          v-for="nation in nationList"
          :key="nation.nation"
          :class="['nation-row', 's-border-b', toggleZoom ? 'on-zoom' : 'on-fit']"
        >
          <div
            :style="{
              backgroundColor: nation.color,
              color: isBrightColor(nation.color) ? 'black' : 'white',
              fontSize: '1.3em',
            }"
            class="d-grid"
          >
            <div class="align-self-center center">
              {{ nation.name }}
            </div>
          </div>
          <div class="nation-scout-plate align-self-center">
            <!-- eslint-disable-next-line vue/no-v-html -->
            <div class="nation-scout-msg" v-html="nation.scoutmsg ?? '-'" />
          </div>
        </div>
      </template>
    </div>

    <!-- 국가 설명 -->
    <div class="row bg1">
      <div class="col center">장수 생성</div>
    </div>
    <div class="forms">
      <div class="row">
        <div class="col col-md-4 col-3 a-right align-self-center">장수명</div>
        <div class="col col-md-3 col-9 align-self-center">
          <input v-model="args.name" class="form-control" />
        </div>
        <div class="col col-md-1 col-3 a-right align-self-center">전콘 사용</div>
        <div class="col col-md-4 col-9 align-self-center">
          <img style="height: 64px; width: 64px" :src="iconPath" />
          <label> <input v-model="args.pic" type="checkbox" /> 사용 </label>
        </div>
        <div class="col col-md-4 col-3 align-self-center a-right">성격</div>

        <div class="col col-md-8 col-9 align-self-center">
          <div class="row">
            <div class="col col-md-3 col-4 align-self-center">
              <select v-model="args.character" class="form-select form-inline" style="max-width: 20ch">
                <option v-for="(personalityObj, key) in availablePersonality" :key="key" :value="key">
                  {{ personalityObj.name }}
                </option>
              </select>
            </div>
            <div class="col col-md-9 col-8 align-self-center">
              <small class="text-muted">
                {{ availablePersonality[args.character].info }}
              </small>
            </div>
          </div>
        </div>
      </div>
      <!--<div class="row">
      <div class="col">
        계정관리에서 자신만을 표현할 수 있는 아이콘을 업로드 해보세요!
      </div>
      </div>-->
      <div class="row" style="margin-top: 1em">
        <div class="col col-md-4 col-3 a-right align-self-center">
          능력치
          <br />
          <small class="text-muted">통/무/지</small>
        </div>
        <div class="col col-md-2 col-3 align-self-center">
          <input v-model="args.leadership" type="number" class="form-control" />
        </div>
        <div class="col col-md-2 col-3 align-self-center">
          <input v-model="args.strength" type="number" class="form-control" />
        </div>
        <div class="col col-md-2 col-3 align-self-center">
          <input v-model="args.intel" type="number" class="form-control" />
        </div>
      </div>
      <div class="row" style="margin-top: 1em">
        <div class="col col-md-4 col-3 a-right align-self-center">능력치 조절</div>
        <div class="col col-md-8 col-9">
          <b-button variant="secondary" class="stat-btn" @click="randStatRandom"> 랜덤형 </b-button>
          <b-button variant="secondary" class="stat-btn" @click="randStatLeadPow"> 통솔무력형 </b-button>
          <b-button variant="secondary" class="stat-btn" @click="randStatLeadInt"> 통솔지력형 </b-button>
          <b-button variant="secondary" class="stat-btn" @click="randStatPowInt"> 무력지력형 </b-button>
        </div>
      </div>
    </div>
    <div class="row" style="border-top: solid 1px #aaa; margin-top: 0.5em">
      <div class="col a-center" style="color: orange">
        모든 능력치는 ( {{ stats.min }} &lt;= 능력치 &lt;= {{ stats.max }} ) 사이로 잡으셔야 합니다. <br />그 외의
        능력치는 가입되지 않습니다.
      </div>
    </div>
    <div class="row">
      <div class="col a-center">
        능력치의 총합은 {{ stats.total }} 입니다. 가입후 {{ stats.bonusMin }} ~ {{ stats.bonusMax }} 의 능력치 보너스를
        받게 됩니다. <br />임의의 도시에서 재야로 시작하며 건국과 임관은 게임 내에서 실행합니다.
      </div>
    </div>

    <div class="row bg1">
      <div class="col col-md-11 col-9 center align-self-center">유산 포인트 사용</div>
      <div class="col col-md-1 col-3">
        <label>
          <input v-model="displayInherit" type="checkbox" />
          {{ displayInherit ? "숨기기" : "보이기" }}
        </label>
      </div>
    </div>
    <div v-if="displayInherit" class="inherit-block">
      <div class="row">
        <div class="col">
          <NumberInputWithInfo v-model="inheritTotalPoint" title="보유한 유산 포인트" :readonly="true" />
        </div>
        <div class="col">
          <NumberInputWithInfo v-model="inheritRequiredPoint" title="필요 유산 포인트" :readonly="true" />
        </div>
      </div>
      <hr />
      <div class="row">
        <div class="col col-md-6 col-sm-6 col-12 p-2 align-self-center">
          <div class="row">
            <div class="col col-6 a-right align-self-center">천재로 생성</div>
            <div class="col col-6 align-self-center">
              <select v-model="args.inheritSpecial" class="form-select form-inline" style="max-width: 20ch">
                <option :value="undefined">사용안함</option>
                <option v-for="(inheritSpecial, key) in availableInheritSpecial" :key="key" :value="key">
                  {{ inheritSpecial.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="col align-self-center">
            <!-- eslint-disable-next-line vue/no-v-html -->
            <small class="text-muted" v-html="availableInheritSpecial[args.inheritSpecial ?? '']?.info" />
          </div>
        </div>

        <div class="col col-md-6 col-sm-6 col-12 p-2 align-self-center">
          <div class="row">
            <div class="col col-6 a-right align-self-center">도시</div>
            <div class="col col-6 align-self-center">
              <select v-model="args.inheritCity" class="form-select form-inline" style="max-width: 20ch">
                <option :value="undefined">사용안함</option>
                <option v-for="city in availableInheritCity" :key="city[0]" :value="city[0]">
                  {{ `[${city[1]}] ${city[2]}` }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <div class="col col-md-6 col-sm-6 col-12 p-2 align-self-center">
          <div class="a-center">
            <label> <input v-model="inheritTurnTimeSet" type="checkbox" />턴 시간 고정 </label>
          </div>
          <div class="row turn_time_pad">
            <div class="col col-md-4 offset-md-3 col-4 offset-3">
              <NumberInputWithInfo
                v-model="inheritTurnTimeMinute"
                :readonly="!inheritTurnTimeSet"
                :min="0"
                :max="1 - turnterm"
                :right="true"
                title="분"
              />
            </div>
            <div class="col col-md-4 col-4">
              <NumberInputWithInfo
                v-model="inheritTurnTimeSecond"
                :readonly="!inheritTurnTimeSet"
                :min="0"
                :max="60"
                :right="true"
                title="초"
              />
            </div>
          </div>
        </div>

        <div class="col col-md-6 col-12 p-2">
          <div class="a-center">추가 능력치 고정</div>
          <div class="row">
            <div class="col">
              <NumberInputWithInfo v-model="(args.inheritBonusStat ?? [0, 0, 0])[0]" title="통솔" />
            </div>
            <div class="col">
              <NumberInputWithInfo v-model="(args.inheritBonusStat ?? [0, 0, 0])[1]" title="무력" />
            </div>
            <div class="col">
              <NumberInputWithInfo v-model="(args.inheritBonusStat ?? [0, 0, 0])[2]" title="지력" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row" style="border-top: solid 1px #aaa">
      <div class="col a-center" style="margin: 0.5em">
        <b-button color="primary" @click="submitForm"> 장수 생성 </b-button>&nbsp;
        <b-button color="secondary" @click="resetArgs"> 다시 입력 </b-button>
      </div>
    </div>
  </div>
</template>
<script lang="ts">
import "@scss/common/bootstrap5.scss";
import "@scss/game_bg.scss";

import { defineComponent } from "vue";
import TopBackBar from "@/components/TopBackBar.vue";
import { getIconPath } from "@util/getIconPath";
import { isBrightColor } from "@util/isBrightColor";
import { abilityLeadint, abilityLeadpow, abilityPowint, abilityRand } from "@util/generalStats";
import { clone, shuffle, sum } from "lodash";
import NumberInputWithInfo from "@/components/NumberInputWithInfo.vue";
import { SammoAPI } from "./SammoAPI";

declare const nationList: {
  nation: number;
  name: string;
  color: string;
  scout: number;
  scoutmsg?: string;
}[];
declare const availablePersonality: {
  [key: string]: {
    name: string;
    info: string;
  };
};

declare const availableInheritSpecial: {
  [key: string]: {
    name: string;
    info: string;
  };
};

declare const availableInheritCity: [number, string, string][];

declare const turnterm: number;

declare const member: {
  name: string;
  grade: number;
  picture: string;
  imgsvr: 0 | 1;
};

declare const stats: {
  min: number;
  max: number;
  total: number;
  bonusMin: number;
  bonusMax: number;
};

declare const inheritPoints: {
  special: number;
  turnTime: number;
  city: number;
  stat: number;
};

declare const serverID: string;
declare const inheritTotalPoint: number;

declare module "@vue/runtime-core" {
  interface ComponentCustomProperties {
    member: typeof member;
  }
}

type APIArgs = {
  name: string;
  leadership: number;
  strength: number;
  intel: number;
  pic: boolean;
  character: string;
  inheritSpecial?: string;
  inheritTurntime?: number;
  inheritCity?: number;
  inheritBonusStat?: [number, number, number];
};

export default defineComponent({
  name: "PageJoin",
  components: {
    TopBackBar,
    NumberInputWithInfo,
  },
  data() {
    const displayTable = JSON.parse(localStorage.getItem(`conf.${serverID}.join.displayTable`) ?? "true");
    const displayInherit = JSON.parse(localStorage.getItem(`conf.${serverID}.join.displayInherit`) ?? "true");
    const nationListShuffled = shuffle(nationList);
    const args: APIArgs = {
      name: member.name,
      leadership: stats.total - 2 * Math.floor(stats.total / 3),
      strength: Math.floor(stats.total / 3),
      intel: Math.floor(stats.total / 3),
      pic: true,
      character: "Random",

      inheritCity: undefined,
      inheritBonusStat: [0, 0, 0],
      inheritSpecial: undefined,
      inheritTurntime: undefined,
    };
    availableInheritCity.sort((lhs, rhs) => {
      const rC = lhs[1].localeCompare(rhs[1]);
      if (rC != 0) return rC;
      return lhs[2].localeCompare(rhs[2]);
    });
    return {
      displayTable,
      displayInherit,
      availablePersonality,
      availableInheritSpecial,
      availableInheritCity,
      member: member,
      stats,
      args,
      nationList: nationListShuffled,
      isBrightColor,
      inheritTotalPoint,
      inheritTurnTimeSet: false,
      inheritTurnTimeMinute: 0,
      inheritTurnTimeSecond: 0,
      turnterm,
      toggleZoom: true,
    };
  },
  computed: {
    iconPath() {
      if (this.args.pic) {
        return getIconPath(this.member.imgsvr, this.member.picture);
      }
      return getIconPath(0, "default.jpg");
    },
    inheritRequiredPoint() {
      let inheritRequiredPoint = 0;
      if (this.args.inheritCity !== undefined) {
        inheritRequiredPoint += inheritPoints.city;
      }
      if (this.args.inheritSpecial !== undefined) {
        inheritRequiredPoint += inheritPoints.special;
      }
      if (this.args.inheritTurntime !== undefined) {
        inheritRequiredPoint += inheritPoints.turnTime;
      }
      if (this.args.inheritBonusStat !== undefined && sum(this.args.inheritBonusStat) != 0) {
        inheritRequiredPoint += inheritPoints.stat;
      }
      return inheritRequiredPoint;
    },
  },
  watch: {
    displayTable(newValue: boolean) {
      localStorage.setItem(`conf.${serverID}.join.displayTable`, JSON.stringify(newValue));
    },
    displayInherit(newValue: boolean) {
      localStorage.setItem(`conf.${serverID}.join.displayInherit`, JSON.stringify(newValue));
    },
    inheritTurnTimeMinute(newValue: number) {
      if (!this.inheritTurnTimeSet) {
        this.args.inheritTurntime = undefined;
        return;
      }
      this.args.inheritTurntime = newValue * 60 + this.inheritTurnTimeSecond;
    },
    inheritTurnTimeSecond(newValue: number) {
      if (!this.inheritTurnTimeSet) {
        this.args.inheritTurntime = undefined;
        return;
      }
      this.args.inheritTurntime = this.inheritTurnTimeMinute * 60 + newValue;
    },
    inheritTurnTimeSet(newValue: boolean) {
      if (!newValue) {
        this.args.inheritTurntime = undefined;
        return;
      }
      this.args.inheritTurntime = this.inheritTurnTimeMinute * 60 + this.inheritTurnTimeSecond;
    },
  },
  methods: {
    randStatRandom() {
      [this.args.leadership, this.args.strength, this.args.intel] = abilityRand();
    },
    randStatLeadPow() {
      [this.args.leadership, this.args.strength, this.args.intel] = abilityLeadpow();
    },
    randStatLeadInt() {
      [this.args.leadership, this.args.strength, this.args.intel] = abilityLeadint();
    },
    randStatPowInt() {
      [this.args.leadership, this.args.strength, this.args.intel] = abilityPowint();
    },
    resetArgs() {
      this.args.name = member.name;
      this.args.pic = true;
      this.args.character = "Random";
      this.args.leadership = stats.total - 2 * Math.floor(stats.total / 3);
      this.args.strength = Math.floor(stats.total / 3);
      this.args.intel = Math.floor(stats.total / 3);
    },
    async submitForm() {
      //검증은 언제 되어야 하는가?
      const args = clone(this.args);
      const totalStat = args.leadership + args.strength + args.intel;

      if (totalStat < stats.total) {
        if (
          !confirm(`설정한 능력치가 ${totalStat}으로, 실제 최대치인 ${stats.total}보다 적습니다.\r\n그래도 진행할까요?`)
        ) {
          return false;
        }
      }
      try {
        await SammoAPI.General.Join(args);
      } catch (e) {
        console.error(e);
        alert(`실패했습니다: ${e}`);
        return;
      }

      alert("정상적으로 생성되었습니다. \n위키와 팁/강좌 게시판을 꼭 읽어보세요!");
      location.href = "./";
    },
  },
});
</script>
<style lang="scss">
@import "@scss/common/bootstrap5.scss";
@import "@scss/editor_component.scss";

#container {
  width: 100%;
  max-width: 1000px;
  margin: auto;
  border: solid 1px #888888;
  overflow: hidden;
}

.forms {
  padding: 0 6px;
}

.stat-btn {
  width: 8em;
  margin-right: 1px;
  margin-bottom: 1px;
}

.col-form-label {
  text-align: right;
}

.turn_time_pad .col-form-label {
  text-align: left;
}
</style>

<style lang="scss" scoped>
@import "@scss/common/break_500px.scss";

@include media-1000px {
  .nation-list .nation-row {
    display: grid;
    grid-template-columns: 130px 870px;
  }

  .zoom-toggle {
    display: none;
  }

  .zoom-toggle > * {
    display: none;
  }
}

@include media-500px {
  .nation-list .nation-row.nation-header {
    display: grid;
    grid-template-columns: 3fr 1fr 1fr;
    grid-template-rows: 1fr 1fr;

    .display-toggle {
      grid-column: 2/3;
      grid-row: 1/3;
    }
    .zoom-toggle {
      grid-column: 3/4;
      grid-row: 1/3;
    }
  }

  .nation-list .nation-row {
    display: grid;
    grid-template-columns: 1fr;
    grid-template-rows: 1fr minmax(1fr, calc(200px * 500 / 870));
  }

  .on-fit {
    .nation-scout-plate {
      max-height: calc(200px * 500 / 870);
      overflow: hidden;
    }

    .nation-scout-msg {
      width: 870px;
      transform-origin: 0px 0px;
      transform: scale(calc(500 / 870));
    }
  }

  .on-zoom {
    .nation-scout-plate {
      max-height: 200px;
      overflow-y: hidden;
      overflow-x: auto;
    }

    .nation-scout-msg {
      max-width: 870px;
    }
  }
}
</style>
